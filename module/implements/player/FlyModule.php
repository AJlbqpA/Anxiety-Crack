<?php

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\DataPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class FlyModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "Fly",
            "fly",
            ModuleCategory::PLAYER,
            "Включает вам режим §aПОЛЕТА"
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->getClientPlayer()->setAllowFlight(true);
    }

    public function onDisable(array $sendParameters = []): void
    {
        $this->getClientPlayer()->setAllowFlight(false);
    }

    public function onServerPacketReceive(DataPacket $packet): void
    {
        if($packet instanceof AdventureSettingsPacket){
            if(!$packet->allowFlight or !$packet->isFlying){
                $this->cancelPacket();
            }
        }
    }

}