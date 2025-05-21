<?php

use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\TextPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/StringParameter.php");

class TimeModule extends IModule
{

    private string $time = "day";

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "Time",
            "time",
            ModuleCategory::VISUAL,
            "Визуально изменяет время суток",
            [
                new StringParameter("время", ["day", "night", "midnight", "noon", "sunrise"])
            ]
        );
    }

    private function updateTime(): void
    {
        match($this->time){
            "day" => $time = Level::TIME_DAY,
            "night" => $time = Level::TIME_NIGHT,
            "midnight" => $time = Level::TIME_MIDNIGHT,
            "noon" => $time = Level::TIME_NOON,
            "sunrise" => $time = Level::TIME_SUNRISE,
            default => $time = 0
        };

        $packet = new SetTimePacket();
        $packet->time = $time;
        $this->getClientPlayer()->dataPacket($packet);
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->time = $sendParameters[0];

        $this->updateTime();

        $this->getClientPlayer()->sendMessage(ModuleCategory::convertToCategoryName($this->getCategory()) . " §r§7➾ §fВремя суток было изменено!");
    }

    public function onServerPacketReceive(DataPacket $packet): void
    {
        if($packet instanceof SetTimePacket){
            $this->cancelPacket();
            $this->updateTime();
        }
    }

}