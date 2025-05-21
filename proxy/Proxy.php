<?php

use pocketmine\block\Block;
use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\network\Network;
use pocketmine\utils\Terminal;

require_once("./util/wrapper/FFIWrapper.php");
require_once("./util/Logger.php");
require_once("./util/service/ConfigService.php");
require_once("./callback/client/ClientPacketSendCallback.php");
require_once("./callback/server/ServerPacketReceiveCallback.php");
require_once("./entity/ServerEntities.php");
require_once("./module/ModuleProvider.php");
require_once("./callback/TickerCallback.php");
require_once("./util/chunkrestorer/ChunkRestorer.php");
require_once("./util/device/DeviceUtil.php");
require_once("./friend/FriendManager.php");

final class Proxy
{

    private FFIWrapper $FFIWrapper;
    private Logger $logger;
    private ConfigService $configService;
    private ChunkRestorer $chunkRestorer;

    private ServerEntities $serverEntities;

    private ModuleProvider $moduleProvider;

    private Network $network;

    private TickerCallback $ticker;

    public function __construct()
    {
        Terminal::init();
        Block::init();
        Entity::init();
        Attribute::init();
        Enchantment::init();
        Item::init();
        Armor::init();

        ini_set('memory_limit', '-1');
        set_time_limit(-1);
        date_default_timezone_set("Europe/Moscow");
        define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\00" ? 0x00 : 0x01));
        define("INT32_MASK", is_int(0xffffffff) ? 0xffffffff : -1);

        new FriendManager("./friends/");
        $this->FFIWrapper = new FFIWrapper();
        $this->logger = new Logger();
        $this->configService = new ConfigService("./");
        $this->chunkRestorer = new ChunkRestorer();

        $this->network = new Network();

        $this->subscribeCallbacks();

        $this->serverEntities = new ServerEntities($this);

        $this->moduleProvider = new ModuleProvider($this);

        $this->ticker = new TickerCallback($this);

        $library = $this->getFFIWrapper()->getLibrary();

        $os = (string) $this->configService->getKey("DeviceOS");

        $library->setDeviceOS((int) $os);

        $deviceModel = $this->configService->getKey("DeviceModel");
        $models = $this->getConfigService()->getDeviceModelsConfig()->getAll();
        if(in_array($os, $models)){
            if($models[$os][$deviceModel] === null){
                $this->selectRandomDeviceModel($library, $os);
            }else{
                $library->setDeviceModel(strtoupper($models[$os][$deviceModel]));
            }
        }else{
            $this->selectRandomDeviceModel($library, $os);
        }

        $this->selectInputModeForOS($library, (int) $os);

        $this->getLogger()->message("Прокси-сервер запущен и ждет входа");
        $library->startProxy($this->configService->getKey("ServerAddress"));
    }


    private function selectRandomDeviceModel(\FFI $library, int $forOS): void
    {
        $this->getLogger()->message("Указанная модель устройства не распознана, устанавливаю случайную для " . DeviceUtil::convertIdToString($forOS) . "...");

        $models = $this->getConfigService()->getDeviceModelsConfig()->getAll()[$forOS];

        foreach($models as $name => $model){
            $this->getLogger()->message("Установил модель: " . $model);
            $library->setDeviceModel(strtoupper($model));
            break;
        }
    }

    private function selectInputModeForOS(\FFI $library, int $os): void
    {
        $mode = DeviceUtil::convertOSToInputMode($os);

        $library->setInputMode($mode);
        $library->setDefaultInputMode($mode);

        $this->getLogger()->message("Установил InputMode для устройства " . DeviceUtil::convertIdToString($os) . ": " . DeviceUtil::convertInputModeToString($mode));

    }

    private function subscribeCallbacks(): void
    {
        $network = $this->network;
        $clientPacketSendCallback = new ClientPacketSendCallback($this);
        $serverPacketReceiveCallback = new ServerPacketReceiveCallback($this);

        $library = $this->getFFIWrapper()->getLibrary();
        $library->subscribeOnClientPacketSend(function($payload, $length) use ($network, &$library, &$clientPacketSendCallback){
            return call_user_func(function(string $buffer, int $len) use (&$network, &$library, &$clientPacketSendCallback) {
                $payload = ord($buffer[0]);
                if(($packet = $network->getPacket(ord($buffer[0]))) != null) {
                    $packet->buffer = substr($buffer, 1);
                    try {
                        $packet->decode();
                    } catch(\Throwable $t) {
                        return true;
                    }
                    $clientPacketSendCallback->handleDataPacket($packet);
                    return true;
                }
                return true;
            }, \FFI::string($payload, $length), $length);
        });
        $library->subscribeOnServerPacketReceive(function($payload, $length) use ($network, &$library, &$serverPacketReceiveCallback){
            return call_user_func(function(string $buffer, int $len) use (&$network, &$library, &$serverPacketReceiveCallback) {
                $payload = ord($buffer[0]);
                if(($packet = $network->getPacket(ord($buffer[0]))) != null) {
                    $packet->buffer = substr($buffer, 1);
                    try {
                        $packet->decode();
                    } catch(\Throwable $t) {
                        return true;
                    }
                    $serverPacketReceiveCallback->handleDataPacket($packet);
                    return true;
                }
                return true;
            }, \FFI::string($payload, $length), $length);
        });
        $library->subscribeOnClientDisconnected(function(int $reason)
        {
            $this->getLogger()->message("Игрок отключился от сервера: сам");
        });
        $library->subscribeOnServerDisconnected(function(int $reason)
        {
            $this->getLogger()->message("Игрок отключился от сервера: сервер");
        });
    }

    public function getNetwork(): Network
    {
        return $this->network;
    }

    public function getFFIWrapper(): FFIWrapper
    {
        return $this->FFIWrapper;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function getConfigService(): ConfigService
    {
        return $this->configService;
    }

    public function getServerEntities(): ServerEntities
    {
        return $this->serverEntities;
    }

    public function getModuleProvider(): ModuleProvider
    {
        return $this->moduleProvider;
    }

    public function getChunkRestorer(): ChunkRestorer
    {
        return $this->chunkRestorer;
    }

    public function getTicker(): TickerCallback
    {
        return $this->ticker;
    }

}