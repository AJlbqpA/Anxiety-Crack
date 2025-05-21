<?php

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\entity\Attribute;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/IntParameter.php");

class NoHungerModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "NoHunger",
            "noh",
            ModuleCategory::PLAYER,
            "Позволяет §aБЕГАТЬ §fбудучи §aГОЛОДНЫМ"
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->getClientPlayer()->setFood(7, false);
    }

    public function onDisable(array $sendParameters = []): void
    {
    }

    public function onServerPacketReceive(DataPacket $packet): void
    {
        if($packet instanceof UpdateAttributesPacket){
            foreach($packet->entries as $index => $attribute){
                if($attribute->getName() === Attribute::getAttribute(Attribute::HUNGER)){
                    $this->cancelPacket();
                    $packet->entries[$index] = Attribute::getAttribute(Attribute::HUNGER)->setValue(7);
                    return;
                }
            }
        }
    }

}