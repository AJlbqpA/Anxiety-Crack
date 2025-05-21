<?php

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/StringParameter.php");

class AntiBotModule extends IModule
{

    public string $bypass = "vanilla";

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "AntiBot",
            "ab",
            ModuleCategory::COMBAT,
            "Отключает §aБОТОВ §fна сервере",
            [
                new StringParameter("обход", ["vanilla", "breadix"])
            ]
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->bypass = $sendParameters[0];

        $this->getClientPlayer()->sendMessage("§cAntiBot находится в разработке!");

        $this->setEnabled(false);
    }

    public function onDisable(array $sendParameters = []): void
    {
    }

}