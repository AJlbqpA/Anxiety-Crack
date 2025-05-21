<?php

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\entity\Attribute;

require_once("./level/Level.php");
require_once("./inventory/ClientPlayerInventory.php");

class ClientPlayerEntity
{

    private Proxy $proxy;
    public Vector3 $position;
    public int $eid = 0;
    public int $gamemode = 0;
    public int $yaw = 0;
    public int $pitch = 0;

    public float $motionX = 0;
    public float $motionY = 0;
    public float $motionZ = 0;

    public int $currentWindowId = 0;

    private bool $isSprinting = false;
    private bool $isSneaking = false;

    private Level $level;

    private ClientPlayerInventory $inventory;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
        $this->position = new Vector3(0, 0, 0);
        $this->inventory = new ClientPlayerInventory($this);
        $this->level = new Level();
    }

    public function sendMessage(string $message, int $type = TextPacket::TYPE_CHAT): void
    {
        $packet = new TextPacket();
        $packet->type = $type;
        $packet->message = $message;
        $this->dataPacket($packet);
    }

    public function sendTitle(string $title, string $message = null): void
    {
        $packet = new SetTitlePacket();
        $packet->title = $title;
        $packet->type = SetTitlePacket::TYPE_TITLE;
        $packet->duration = -1;
        $packet->fadeInDuration = -1;
        $packet->fadeOutDuration = -1;
        $this->dataPacket($packet);

        if(!is_null($message)){
            $packet = new SetTitlePacket();
            $packet->title = $message;
            $packet->type = SetTitlePacket::TYPE_SUB_TITLE;
            $packet->duration = -1;
            $packet->fadeInDuration = -1;
            $packet->fadeOutDuration = -1;
            $this->dataPacket($packet);
        }
    }

    public function jump(): void
    {
        $packet = new PlayerActionPacket();
        $packet->eid = $this->getEID();
        $packet->action = PlayerActionPacket::ACTION_JUMP;
        $packet->x = $this->position->x;
        $packet->y = $this->position->y;
        $packet->z = $this->position->z;
        $packet->face = 0;
        $this->dataPacketToServer($packet);
    }

    public function setAllowFlight(bool $value): void
    {
        $packet = new AdventureSettingsPacket();
        $packet->userPermission = AdventureSettingsPacket::PERMISSION_NORMAL;
        $packet->allowFlight = $value;
        $this->dataPacket($packet);
    }

    public function attackEntity(int $eid): void
    {
        $packet = new InteractPacket();
        $packet->action = InteractPacket::ACTION_LEFT_CLICK;
        $packet->target = $eid;
        $this->dataPacketToServer($packet);

        $packet = new AnimatePacket();
        $packet->action = AnimatePacket::ACTION_SWING_ARM;
        $packet->eid = $this->eid;
        $this->dataPacketToServer($packet);
        $this->dataPacket($packet);

    }

    public function transferTo(string $address): void
    {
        $this->proxy->getFFIWrapper()->getLibrary()->transferTo($address);
    }

    public function setVisualGameMode(int $mode): void
    {
        $packet = new SetPlayerGameTypePacket();
        $packet->gamemode = $mode;
        $this->dataPacket($packet);
    }

    public function setFood(int $food, bool $toServer = true): void
    {
        $packet = new UpdateAttributesPacket();
        $packet->entityId = $this->getEID();
        $packet->entries[] = Attribute::getAttribute(Attribute::HUNGER)->setValue($food);
        $this->dataPacket($packet);
        if($toServer){
            $this->dataPacketToServer($packet);
        }
    }


    public function setHeadRotation(int $yaw, int $pitch): void
    {
        $packet = new MovePlayerPacket();
        $packet->yaw = $yaw;
        $packet->pitch = $pitch;
        $packet->x = $this->position->x;
        $packet->y = $this->position->y;
        $packet->z = $this->position->z;
        $packet->bodyYaw = $yaw;
        $packet->eid = $this->eid;
        $packet->mode = MovePlayerPacket::MODE_PITCH;
        $this->dataPacket($packet);
        $this->dataPacketToServer($packet);
    }

    public function rotateTo(Vector3 $position): void
    {
        $currentPosition = $this->position;

        $dx = $position->x - $currentPosition->x;
        $dz = $position->z - $currentPosition->z;
        $dy = $position->y - $currentPosition->y;

        $yaw = rad2deg(atan2($dz, $dx)) - 90;
        $yaw = ($yaw + 360) % 360;

        $horizontalDistance = sqrt($dx * $dx + $dz * $dz);
        $pitch = rad2deg(atan2($dy, $horizontalDistance));

        $this->setHeadRotation((int) $yaw, (int) $pitch);
    }

    public function setMotion(Vector3 $motion): void
    {
        $packet = new SetEntityMotionPacket();
        $packet->motionX = $motion->x;
        $packet->motionY = $motion->y;
        $packet->motionZ = $motion->z;
        $packet->eid = $this->eid;
        $this->dataPacketToServer($packet);
        $this->dataPacket($packet);
    }

    public function setSneaking(bool $value, bool $inGame = true): void
    {
        $this->isSneaking = $value;

        if($inGame){
            $packet = new PlayerActionPacket();
            $packet->eid = $this->getEID();
            $packet->action = $value ? PlayerActionPacket::ACTION_START_SNEAK : PlayerActionPacket::ACTION_STOP_SNEAK;
            $packet->x = $this->position->x;
            $packet->y = $this->position->y;
            $packet->z = $this->position->z;
            $packet->face = 0;
            $this->dataPacketToServer($packet);
        }
    }

    public function setSprinting(bool $value, bool $inGame = true): void
    {
        $this->isSprinting = $value;

        if($inGame){
            $packet = new PlayerActionPacket();
            $packet->eid = $this->getEID();
            $packet->action = $value ? PlayerActionPacket::ACTION_START_SPRINT : PlayerActionPacket::ACTION_STOP_SPRINT;
            $packet->x = $this->position->x;
            $packet->y = $this->position->y;
            $packet->z = $this->position->z;
            $packet->face = 0;
            $this->dataPacketToServer($packet);
        }
    }

    public function closeWindow(int $window) : void
    {
        if($window === 0) return;
        $packet = new ContainerClosePacket();
        $packet->windowid = $window;
        $this->dataPacket($packet);
        $this->dataPacketToServer($packet);
    }

    public function isOnBlockEdge(float $edgeThreshold = 0.3): bool {
        $pos = $this->position;

        $blockX = (int)floor($pos->x);
        $blockY = (int)floor($pos->y - 0.1);
        $blockZ = (int)floor($pos->z);

        $fracX = $pos->x - $blockX;
        $fracZ = $pos->z - $blockZ;

        $onEdgeX = ($fracX < $edgeThreshold) || ($fracX > (1 - $edgeThreshold));
        $onEdgeZ = ($fracZ < $edgeThreshold) || ($fracZ > (1 - $edgeThreshold));

        return $onEdgeX || $onEdgeZ;
    }

    public function getDirectionVector() : Vector3{
        $y = -sin(deg2rad($this->pitch));
        $xz = cos(deg2rad($this->pitch));
        $x = -$xz * sin(deg2rad($this->yaw));
        $z = $xz * cos(deg2rad($this->yaw));

        return (new Vector3($x, $y, $z))->normalize();
    }

    public function dataPacket(DataPacket $packet): void
    {
        if(!$packet->isEncoded) $packet->encode();
        $this->proxy->getFFIWrapper()->getLibrary()->sendToClient($packet->buffer, strlen($packet->buffer));
    }

    public function dataPacketToServer(DataPacket $packet): void
    {
        if(!$packet->isEncoded) $packet->encode();
        $this->proxy->getFFIWrapper()->getLibrary()->sendToServer($packet->buffer, strlen($packet->buffer));
    }

    public function getGameMode(): int
    {
        return $this->gamemode;
    }

    public function isSneaking(): bool
    {
        return $this->isSneaking;
    }

    public function isSprinting(): bool
    {
        return $this->isSprinting;
    }

    public function getMotion(): Vector3
    {
        return new Vector3($this->motionX, $this->motionY, $this->motionZ);
    }

    public function getEID(): int
    {
        return $this->eid;
    }

    public function getInventory(): ClientPlayerInventory
    {
        return $this->inventory;
    }

    public function getCurrentWindowId(): int
    {
        return $this->currentWindowId;
    }

    public function getLevel(): Level
    {
        return $this->level;
    }

    public function getPosition(): Vector3
    {
        return $this->position;
    }

}