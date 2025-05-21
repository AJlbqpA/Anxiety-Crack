<?php

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/IntParameter.php");
require_once("./module/parameters/StringParameter.php");

class PositionModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "Position",
            "pos",
            ModuleCategory::UTILITY,
            "Показывает вашу §aПОЗИЦИЮ"
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->getClientPlayer()->sendMessage(ModuleCategory::convertToCategoryName($this->getCategory()) . " §r§7➾ §r§fВаша позиция: §a" . (int) $this->getClientPlayer()->position->x . " " . (int) $this->getClientPlayer()->position->y . " " . (int) $this->getClientPlayer()->position->z);

        $this->setEnabled(false);
    }

}