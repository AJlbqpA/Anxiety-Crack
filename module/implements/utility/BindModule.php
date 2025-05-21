<?php

use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\TextPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/IntParameter.php");
require_once("./module/parameters/StringParameter.php");

class BindModule extends IModule
{

    private string $nameModule = "";
    private int $slot = 0;

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "Bind",
            "bind",
            ModuleCategory::UTILITY,
            "Устанавливает §aБИНД§f на модуль",
            [
                new StringParameter("название модуля"),
                new IntParameter("слот", -1, 9)
            ]
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->nameModule = $sendParameters[0];
        $this->slot = (int) $sendParameters[1];

        $module = $this->getProxy()->getModuleProvider()->getModule($this->nameModule);
        if(!($module instanceof IModule)){
            $this->getClientPlayer()->sendMessage(ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) . " §r§7➾ §r§fМодуль не существует!");
            return;
        }

        $module->bind = $this->slot;

        $this->setEnabled(false);
    }

}