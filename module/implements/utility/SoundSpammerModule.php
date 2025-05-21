<?php

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class SoundSpammerModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "SoundSpammer",
            "sspam",
            ModuleCategory::UTILITY,
            "Спамит §aЗВУКАМИ §fвокруг"
        );
    }

    public function onClientPacketSend(DataPacket $packet): void
    {
        if($packet instanceof MovePlayerPacket or $packet instanceof MoveEntityPacket){
            $pk = new PlaySoundPacket();
            $pk->sound = "entity.enderdragon.death";
            $pk->x = $this->getClientPlayer()->getPosition()->getX();
            $pk->y = $this->getClientPlayer()->getPosition()->getY();
            $pk->z = $this->getClientPlayer()->getPosition()->getZ();
            $pk->volume = 100.00;
            $pk->float = 2.00;
            $this->getClientPlayer()->dataPacketToServer($pk);
        }
    }

}