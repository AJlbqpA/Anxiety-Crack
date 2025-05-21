<?php

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/IntParameter.php");
require_once("./module/parameters/StringParameter.php");

class ScaffoldModule extends IModule
{
    private float $lastPlace = 0;

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "Scaffold",
            "scf",
            ModuleCategory::PLAYER,
            "Строит автоматический мост, находя блоки в инвентаре"
        );
    }

    public function onEnable(array $parameters = []): void
    {
    }

    public function onDisable(array $parameters = []): void
    {
    }

    public function onClientPacketSend(DataPacket $packet): void
    {
        if(!$packet instanceof MovePlayerPacket || $packet->mode !== MovePlayerPacket::MODE_NORMAL) {
            return;
        }

        $now = microtime(true);
        if($now - $this->lastPlace < 0.1) {
            return;
        }

        if($this->canPlaceBlockLegit()) {
            $this->placeBlockLegit();
            $this->lastPlace = $now;
        }
    }

    private function findSuitableBlockSlot(): ?int
    {
        $inventory = $this->getClientPlayer()->getInventory();
        $currentSlot = $inventory->getCurrentSlot();
        $currentItem = $inventory->getItem($currentSlot);

        if($currentItem->getId() !== BlockIds::AIR && Block::get($currentItem->getId())->isSolid()) {
            return $currentSlot;
        }

        for($slot = 0; $slot < 9; $slot++) {
            $item = $inventory->getItem($slot);
            if($item->getId() !== BlockIds::AIR && Block::get($item->getId())->isSolid()) {
                return $slot;
            }
        }

        return null;
    }

    public function canPlaceBlockLegit(): bool
    {
        if($this->getClientPlayer()->getGameMode() !== 0 || !$this->getClientPlayer()->isSneaking()) {
            return false;
        }

        $slot = $this->findSuitableBlockSlot();
        if($slot === null || !$this->getClientPlayer()->isOnBlockEdge()) {
            return false;
        }

        $direction = $this->getClientPlayer()->getDirectionVector();
        $targetX = (int)floor($this->getClientPlayer()->getPosition()->getX() + $direction->x);
        $targetY = (int)floor($this->getClientPlayer()->getPosition()->getY() + $direction->y);
        $targetZ = (int)floor($this->getClientPlayer()->getPosition()->getZ() + $direction->z);

        $targetBlock = $this->getClientPlayer()->getLevel()->getBlock($targetX, $targetY, $targetZ);
        if($targetBlock->getId() !== BlockIds::AIR) {
            return false;
        }

        $blockUnderTarget = $this->getClientPlayer()->getLevel()->getBlock($targetX, $targetY - 1, $targetZ);
        return $blockUnderTarget->isSolid();
    }

    public function placeBlockLegit(): bool
    {
        $slot = $this->findSuitableBlockSlot();
        if($slot === null) {
            return false;
        }

        if($slot !== $this->getClientPlayer()->getInventory()->getCurrentSlot()) {
            $this->getClientPlayer()->getInventory()->selectSlot($slot);
        }

        $direction = $this->getClientPlayer()->getDirectionVector();
        $targetX = (int)floor($this->getClientPlayer()->getPosition()->getX() + $direction->x);
        $targetY = (int)floor($this->getClientPlayer()->getPosition()->getY() + $direction->y);
        $targetZ = (int)floor($this->getClientPlayer()->getPosition()->getZ() + $direction->z);

        $face = $this->determinePlaceFace($targetX, $targetY, $targetZ);
        $this->sendBlockPlacePacket($targetX, $targetY, $targetZ, $face);

        return true;
    }

    private function determinePlaceFace(int $x, int $y, int $z): int
    {
        $playerPos = $this->getClientPlayer()->getPosition();
        $dx = $x + 0.5 - $playerPos->x;
        $dy = $y + 0.5 - $playerPos->y;
        $dz = $z + 0.5 - $playerPos->z;

        $absDx = abs($dx);
        $absDy = abs($dy);
        $absDz = abs($dz);

        $max = max($absDx, $absDy, $absDz);

        if($max === $absDy) {
            return $dy > 0 ? 1 : 0;
        } elseif($max === $absDx) {
            return $dx > 0 ? 5 : 4;
        } else {
            return $dz > 0 ? 3 : 2;
        }
    }

    private function sendBlockPlacePacket(int $x, int $y, int $z, int $face): void
    {
        $player = $this->getClientPlayer();

        $packet = new PlayerActionPacket();
        $packet->eid = $player->getEID();
        $packet->action = PlayerActionPacket::ACTION_START_BREAK;
        $packet->x = $x;
        $packet->y = $y;
        $packet->z = $z;
        $packet->face = $face;
        $player->dataPacketToServer($packet);

        $animatePacket = new AnimatePacket();
        $animatePacket->action = AnimatePacket::ACTION_SWING_ARM;
        $animatePacket->eid = $player->getEID();
        $player->dataPacketToServer($animatePacket);
    }
}