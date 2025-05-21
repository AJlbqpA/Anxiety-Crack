<?php

use pocketmine\utils\Config;

class ConfigService
{

    private Config $config;
    private Config $deviceModelsConfigs;

    public function __construct(string $folder)
    {
        if(!(is_dir($folder))) mkdir($folder);

        $this->config = new Config($folder . "settings.json", Config::JSON, [
            "DeviceOS" => 1,
            "DeviceModel" => "XIAOMI NIGHTLY",
            "ServerAddress" => "play.breadixpe.ru:19132"
        ]);
        $this->deviceModelsConfigs = new Config($folder . "resources/device_models.json", Config::JSON, [
            "XIAOMI" => "XIAOMI NIGHTLY"
        ]);

    }

    public function getKey(string $key): mixed
    {
        return $this->config->get($key);
    }

    public function setKey(string $key, mixed $value): void
    {
        $this->config->set($key, $value);
        $this->config->save();
    }

    public function getDeviceModelsConfig(): Config
    {
        return $this->deviceModelsConfigs;
    }

}