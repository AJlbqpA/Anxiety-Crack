<?php

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\TextPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/IntParameter.php");
require_once("./module/parameters/StringParameter.php");

class HitboxModule extends IModule
{

    private int $x;
    private int $y;
    private string $mode = "vanilla";

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "HitBox",
            "hb",
            ModuleCategory::COMBAT,
            "Изменяет §aХИТБОКСЫ§f игроков",
            [
                new IntParameter("x", 0.5, 3),
                new IntParameter("y", 0.5, 3),
                new StringParameter("режим", ["vanilla", "legit"])
            ]
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->x = $sendParameters[0];
        $this->y = $sendParameters[1];
        $this->mode = $sendParameters[2];

        $this->updateHitboxes();

    }

    public function onDisable(array $sendParameters = []): void
    {
        $this->x = 0.8;
        $this->y = 0.8;

        $this->updateHitboxes();

    }

    public function updateHitboxes()
    {
        if($this->isEnabled() === false) return;
        foreach($this->getEntities() as $eid => $entity){
            if($eid === $this->getClientPlayer()->getEID()) continue;

            $packet = new SetEntityDataPacket();
            $packet->eid = $eid;
            $packet->metadata = [
                \pocketmine\entity\Entity::DATA_BOUNDING_BOX_WIDTH => [\pocketmine\entity\Entity::DATA_TYPE_FLOAT, $this->x],
                \pocketmine\entity\Entity::DATA_BOUNDING_BOX_HEIGHT => [\pocketmine\entity\Entity::DATA_TYPE_FLOAT, $this->y]
            ];
            $this->getClientPlayer()->dataPacket($packet);
        }
    }

    public function onClientPacketSend(DataPacket $packet): void
    {
        $player = $this->getClientPlayer();

        if($this->mode === "legit"){
            if($packet instanceof InteractPacket){
                $entity = $this->getProxy()->getServerEntities()->getEntity($packet->target);
                if($entity instanceof Entity){
                    if($this->isEnabled()){
                        if ($player->getPosition()->distanceSquared($entity->getPosition()) > 9){
                            $this->cancelPacket();
                        }
                    }
                }
            }
        }
    }

}