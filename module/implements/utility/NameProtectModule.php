<?php

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\TextPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class NameProtectModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "NameProtect",
            "nprotect",
            ModuleCategory::UTILITY,
            "Скрывает ваш ник"
        );
    }

    public function onServerPacketReceive(DataPacket $packet): void
    {
        if($packet instanceof TextPacket){
        }
    }

}