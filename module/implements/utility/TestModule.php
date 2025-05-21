<?php

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class TestModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "Test",
            "test",
            ModuleCategory::UTILITY,
            "Тестовый модуль",
            [
                new StringParameter("тест строки"),
                new StringParameter("режим", ["test", "legit", "bypass"]),
                new IntParameter("тест цифры")
            ]
        );
    }

}