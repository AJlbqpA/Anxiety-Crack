<?php

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class AntiKnockBackModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "AntiKnockBack",
            "akb",
            ModuleCategory::COMBAT,
            "Отключает ваше §aОТКИДЫВАНИЕ"
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
    }

    public function onDisable(array $sendParameters = []): void
    {
    }

    public function onServerPacketReceive(DataPacket $packet): void
    {
        $player = $this->getClientPlayer();
        if($packet instanceof SetEntityMotionPacket){
            $this->cancelPacket();
        }
    }

}