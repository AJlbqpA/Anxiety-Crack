<?php

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class AutoSprintModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "AutoSprint",
            "as",
            ModuleCategory::MOVEMENT,
            "Включает вам автоматический §aСПРИНТ"
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
    }

    public function onDisable(array $sendParameters = []): void
    {
    }

    public function onClientPacketSend(DataPacket $packet): void
    {
        if($packet instanceof MovePlayerPacket){
            if($packet->teleportCause === MovePlayerPacket::MODE_NORMAL){
                $this->getClientPlayer()->setSprinting(true);
            }
        }
    }

}