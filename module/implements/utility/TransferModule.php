<?php

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class TransferModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "Transfer",
            "tr",
            ModuleCategory::UTILITY,
            "§aПЕРЕНОСИТ §fвас на указанный сервер",
            [
                new StringParameter("айпи"),
                new IntParameter("порт", 10000, 20000)
            ]
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
        $this->setEnabled(false, $sendParameters);
        $this->getClientPlayer()->transferTo($sendParameters[0] . ":" . $sendParameters[1]);
    }

}