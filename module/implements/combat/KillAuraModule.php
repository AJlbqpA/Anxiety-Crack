<?php

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class KillAuraModule extends IModule
{

    public int $cps = 1;

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "KillAura",
            "kl",
            ModuleCategory::COMBAT,
            "Автоматически §aАТАКУЕТ§f сущностей",
            [
                new IntParameter("КПС", 1, 16)
            ]
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->cps = $sendParameters[0];
    }

}