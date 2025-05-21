<?php

use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/IntParameter.php");

class GamemodeModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "GameMode",
            "gm",
            ModuleCategory::PLAYER,
            "Устанавливает вам визуальный §aРЕЖИМ ИГРЫ",
            [
                new IntParameter("режим", 0, 3)
            ]
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $mode = (int) $sendParameters[0];

        $this->getClientPlayer()->setVisualGameMode($mode);
    }

    public function onDisable(array $sendParameters = []): void
    {
        $this->getClientPlayer()->setVisualGameMode($this->getClientPlayer()->getGameMode());
    }

    public function onServerPacketReceive(DataPacket $packet): void
    {
        if($packet instanceof SetPlayerGameTypePacket){
            $this->cancelPacket();
        }
    }

}