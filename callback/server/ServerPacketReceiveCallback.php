<?php

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\FullChunkDataPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\UseItemPacket;

require_once("./proxy/Proxy.php");
require_once("./entity/Entity.php");

class ServerPacketReceiveCallback
{

    private Proxy $proxy;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
    }

    public function handleDataPacket(DataPacket $packet): void
    {
        $player = $this->proxy->getServerEntities()->getClientPlayerEntity();

        if($packet instanceof UpdateBlockPacket){
            $this->proxy->getServerEntities()->getClientPlayerEntity()->getLevel()->updateBlock(
                $packet->x,
                $packet->y,
                $packet->z,
                $packet->blockId
            );
        }
        if($packet instanceof FullChunkDataPacket){
            $this->proxy->getServerEntities()->getClientPlayerEntity()->getLevel()->updateChunk(
                $packet->chunkX,
                $packet->chunkZ,
                $packet->data
            );
        }

        foreach($this->proxy->getModuleProvider()->getModules() as $module){
            if($module instanceof IModule){
                if(!$module->alwaysPacketReceive() and !$module->isEnabled()) continue;
                $module->onServerPacketReceive($packet);
            }
        }

        if($packet instanceof AddEntityPacket) {
            $this->proxy->getServerEntities()->addEntity(new Entity($packet->eid, "unkown"), $packet->metadata);
        }
        if($packet instanceof MovePlayerPacket or $packet instanceof MoveEntityPacket){
            $entity = $this->proxy->getServerEntities()->getEntity($packet->eid);
            if($entity) {
                $entity->updatePosition(new Vector3($packet->x, $packet->y, $packet->z));
            } else {
                $newEntity = new Entity($packet->eid, "unknown");
                $this->proxy->getServerEntities()->addEntity($newEntity);
                $newEntity->updatePosition(new Vector3($packet->x, $packet->y, $packet->z));
            }
        }
        if($packet instanceof RemoveEntityPacket){
            $this->proxy->getServerEntities()->removeEntity($packet->eid);
        }
        if($packet instanceof TransferPacket){
            $this->proxy->getLogger()->message("Игрок переносится на другой сервер: сервер");

            $this->proxy->getServerEntities()->clearEntities();
            $player->getInventory()->items = [];
        }

        if($packet instanceof SetEntityMotionPacket) {
            if($packet->eid === $player->eid){
                $motionX = $packet->motionX;
                $motionY = $packet->motionY;
                $motionZ = $packet->motionZ;

                if($motionX === null) $motionX = 0;
                if($motionY === null) $motionY = 0;
                if($motionZ === null) $motionZ = 0;

                $player->motionX = $motionX;
                $player->motionY = $motionY;
                $player->motionZ = $motionZ;
            }
        }
        if($packet instanceof StartGamePacket) {
            $player->eid = $packet->entityRuntimeId;
        }
        if($packet instanceof SetPlayerGameTypePacket){
            $player->gamemode = $packet->gamemode;
        }
        if($packet instanceof PlayStatusPacket) {
            if($packet->status === PlayStatusPacket::PLAYER_SPAWN){
                $this->proxy->getLogger()->message("Игрок присоединился к игре");
                $player->sendMessage(ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) . " §r§7➾ §fВы успешно зашли на сервер!");
            }
        }
        if($packet instanceof ContainerSetContentPacket) {
            if($packet->targetEid === $player->eid and $packet->windowid === 0){
                foreach ($packet->slots as $slot => $item) {
                    if ($item instanceof Item) {
                        $player->getInventory()->items[$slot] = $item;
                    }
                }
                foreach ($packet->hotbar as $slot => $item) {
                    if ($item instanceof Item) {
                        $player->getInventory()->items[$slot] = $item;
                    }
                }
            }
        }
        if($packet instanceof ContainerSetSlotPacket){
            if($packet->windowid === 0){ // player inventory
                $this->proxy->getServerEntities()->getClientPlayerEntity()->getInventory()->items[$packet->slot] = $packet->item;
            }
        }

        $entity = $this->proxy->getServerEntities()->getEntity($packet->eid ?? 0);
        if($entity) {
            $entity->packetReceivedCount++;
        }

    }

}