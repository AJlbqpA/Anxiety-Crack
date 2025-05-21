<?php

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/IntParameter.php");
require_once("./module/parameters/StringParameter.php");

class JetPackModule extends IModule
{

    private bool $unpressShift = false;

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "JetPack",
            "jp",
            ModuleCategory::PLAYER,
            "Джет-пак"
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
        $player = $this->getClientPlayer();

        if($packet instanceof MovePlayerPacket){
            if($packet->teleportCause === MovePlayerPacket::MODE_NORMAL){
                $player->setMotion($player->getDirectionVector()->add(0.225, 0, 0.225));
            }
        }

    }

}