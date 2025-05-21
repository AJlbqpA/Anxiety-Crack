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

class GlideModule extends IModule
{

    private string $bypass;
    private bool $unpressShift = false;

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "Glide",
            "gl",
            ModuleCategory::PLAYER,
            "Плавное §aПАДЕНИЕ",
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
            if($packet instanceof MovePlayerPacket){
                if(!$this->unpressShift){
                    $packet = new PlayerActionPacket();
                    $packet->eid = $this->getClientPlayer()->eid;
                    $packet->action = PlayerActionPacket::ACTION_START_SNEAK;
                    $packet->x = $this->getClientPlayer()->position->x;
                    $packet->y = $this->getClientPlayer()->position->y;
                    $packet->z = $this->getClientPlayer()->position->z;
                    $packet->face = 0;
                    $this->getClientPlayer()->dataPacketToServer($packet);

                    $this->getClientPlayer()->setMotion(new Vector3($this->getClientPlayer()->motionX * 1.6, $this->getClientPlayer()->motionY, $this->getClientPlayer()->motionZ * 1.6));

                    $this->unpressShift = true;
                    return;
                }
                $packet = new PlayerActionPacket();
                $packet->eid = $this->getClientPlayer()->eid;
                $packet->action = PlayerActionPacket::ACTION_STOP_SNEAK;
                $packet->x = $this->getClientPlayer()->position->x;
                $packet->y = $this->getClientPlayer()->position->y;
                $packet->z = $this->getClientPlayer()->position->z;
                $packet->face = 0;
                $this->getClientPlayer()->dataPacketToServer($packet);

                $this->unpressShift = false;
            }
        }

    }

}