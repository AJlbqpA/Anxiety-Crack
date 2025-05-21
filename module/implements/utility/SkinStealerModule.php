<?php

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class SKinStealerModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "SkinStealer",
            "ssteal",
            ModuleCategory::UTILITY,
            "Выкачивает §aСКИН §fигрока по нажатию"
        );
    }

    public function onClientPacketSend(DataPacket $packet): void
    {
        if($packet instanceof InteractPacket){
            if($packet->action === InteractPacket::ACTION_LEFT_CLICK){
                $eid = $packet->target;

                $this->stealAndSave($eid);

            }
        }
    }

    private function stealAndSave(int $target): void
    {
        $path = "./skinstealer/" . $target . ".png";

        $skinDataCData = $this->getProxy()->getFFIWrapper()->getLibrary()->getPlayerSkinBase64ByEid($target);

        if ($skinDataCData === null) {
            return;
        }

        $skinBase64 = \FFI::string($skinDataCData);

        $skinBinary = base64_decode($skinBase64);

        if ($skinBinary === false) {
            return;
        }

        if (substr($skinBinary, 0, 8) !== "\x89PNG\x0D\x0A\x1A\x0A") {
            $convertedCData = $this->getProxy()->getFFIWrapper()->getLibrary()->convertSkinBase64ToPngBase64($skinDataCData);
            if ($convertedCData !== null) {
                $convertedBase64 = \FFI::string($convertedCData);
                $skinBinary = base64_decode($convertedBase64);
            }
        }

        if (file_put_contents($path, $skinBinary) === false) {
            return;
        }

        $this->getClientPlayer()->sendMessage("Скин сохранен в §a" . $path);
    }

}