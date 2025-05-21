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

class LongJumpModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "LongJump",
            "ljump",
            ModuleCategory::PLAYER,
            "Придает вам §aБУСТ §fк §aПРЫЖКУ §fвперед"
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
        if($packet instanceof PlayerActionPacket){
            if($packet->action === PlayerActionPacket::ACTION_JUMP){
                $direction = $player->getDirectionVector();
                $this->getClientPlayer()->setMotion($this->getClientPlayer()->getDirectionVector()->multiply(0.85));
            }
        }
    }

}