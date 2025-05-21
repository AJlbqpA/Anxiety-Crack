<?php

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/IntParameter.php");
require_once("./module/parameters/StringParameter.php");

class HighJumpModule extends IModule
{

    private string $bypass;

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "HighJump",
            "hjump",
            ModuleCategory::PLAYER,
            "Придает вам §aБУСТ§f к §aПРЫЖКУ §fвверх",
            [
                new StringParameter("обход", ["breadix"])
            ]
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->bypass = $sendParameters[0];
    }

    public function onDisable(array $sendParameters = []): void
    {
    }

    public function onClientPacketSend(DataPacket $packet): void
    {
        if($this->bypass === "breadix"){
            if($packet instanceof PlayerActionPacket){
                if($packet->action === PlayerActionPacket::ACTION_JUMP){
                    $this->getClientPlayer()->setMotion($this->getClientPlayer()->getMotion()->add(0, 0.689, 0));
                }
            }
        }
    }

}