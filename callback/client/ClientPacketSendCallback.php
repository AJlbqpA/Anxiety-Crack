<?php

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\UseItemPacket;

require_once("./module/ModuleCategory.php");
require_once("./proxy/Proxy.php");
require_once("./module/parameters/IParameter.php");
require_once("./module/parameters/StringParameter.php");
require_once("./module/parameters/IntParameter.php");
require_once("./entity/Entity.php");

class ClientPacketSendCallback
{
    private Proxy $proxy;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
    }

    public function handleDataPacket(DataPacket $packet): void
    {
        $player = $this->proxy->getServerEntities()->getClientPlayerEntity();

        foreach ($this->proxy->getModuleProvider()->getModules() as $module) {
            if ($module instanceof IModule) {
                if(!$module->alwaysPacketReceive() and !$module->isEnabled()) continue;
                $module->onClientPacketSend($packet);
            }
        }

        if($packet instanceof MobEquipmentPacket){
                $slot = $packet->slot - 8;

                $player->getInventory()->currentSlot = $slot;
                if($slot <= 9 and $slot >= 1){
                    if($player->isSneaking()){
                        foreach ($this->proxy->getModuleProvider()->getModules() as $module) {
                            if ($module instanceof IModule) {
                                if($module->bind !== -1 and $module->bind === $slot){
                                    if($module->hasParameters()){
                                        if($module->hasLastParameters()){
                                            $module->setEnabled(null, $module->lastParameters);
                                        }else{
                                            $this->proxy->getServerEntities()->getClientPlayerEntity()->sendMessage(ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) . " §r§7➾ §r§fЧтобы бинд работал, нужно включить модуль один раз и более самостоятельно, чтобы установить параметры");
                                        }
                                    }else{
                                        $module->setEnabled(null);
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
        }

        if ($packet instanceof TextPacket && $packet->message[0] === ".") {
            $args = explode(" ", $packet->message);
            $command = $args[0];
            if ($command === ".help") {
                $this->proxy->getFFIWrapper()->getLibrary()->cancelPacket();
                $player->sendMessage(ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) . " §r§7➾ §r§fСписок всех команд:");

                foreach ($this->proxy->getModuleProvider()->getModules() as $module) {
                    if ($module instanceof IModule) {
                        $parameterDescriptions = [];
                        if (!$module->isEnabled() && count($module->getParameters()) > 0) {
                            foreach ($module->getParameters() as $parameter) {
                                if ($parameter instanceof IParameter) {
                                    if ($parameter instanceof StringParameter) {
                                        $templates = !empty($parameter->templates) ? "§r§7[" . implode(", ", $parameter->templates) . "]" : "";
                                        $parameterDescriptions[] = empty($parameter->templates) ? "<" . $parameter->description . "§r§a>" : "<" . $parameter->description . " {$templates}§r§a>";
                                    } elseif ($parameter instanceof IntParameter) {
                                        $parameterDescriptions[] = "<" . $parameter->description . " §r§7[от {$parameter->min} до " . ($parameter->max === PHP_INT_MAX ? "бесконечности" : $parameter->max) . "]§r§a>";
                                    }
                                }
                            }
                            $parametersString = implode(' ', $parameterDescriptions);
                            $player->sendMessage(
                                ModuleCategory::convertToCategoryName($module->getCategory()) .
                                " §r§7➾ §r§a." . $module->getCommand() .
                                " §r§a" . $parametersString . "§r§a" .
                                " §r§7- §r§f" . $module->getDescription() .
                                " §r§7(" . $module->getName() . "§r§7)"
                            );
                            continue;
                        }

                        $player->sendMessage(
                            ModuleCategory::convertToCategoryName($module->getCategory()) .
                            " §r§7➾ §r§a." . $module->getCommand() .
                            " §r§7- §r§f" . $module->getDescription() .
                            " §r§7(" . $module->getName() . "§r§7)"
                        );
                    }
                }
                return;
            }

            $isFinded = false;
            foreach ($this->proxy->getModuleProvider()->getModules() as $module) {
                if ($module instanceof IModule && ("." . $module->getCommand()) === $command) {
                    $isFinded = true;
                    $parameters = [];
                    if (!$module->isEnabled() && count($module->getParameters()) > 0) {
                        if ((count($args) - 1) < count($module->getParameters())) {
                            $player->sendMessage(ModuleCategory::convertToCategoryName($module->getCategory()) . " §r§7➾ §r§fНеверный синтаксис команды! Подробнее в §a.help");
                            $this->proxy->getFFIWrapper()->getLibrary()->cancelPacket();
                            continue;
                        }

                        foreach ($module->getParameters() as $id => $parameter) {
                            if ($parameter instanceof IParameter) {
                                if ($parameter instanceof StringParameter) {
                                    if (!$parameter->isCorrect($args[$id + 1])) {
                                        $player->sendMessage(ModuleCategory::convertToCategoryName($module->getCategory()) . " §r§7➾ §r§fНеверный синтаксис команды! Подробнее в §a.help");
                                        $this->proxy->getFFIWrapper()->getLibrary()->cancelPacket();
                                        return;
                                    }
                                    $parameters[] = $args[$id + 1];
                                } elseif ($parameter instanceof IntParameter) {
                                    if (!$parameter->isCorrect($args[$id + 1])) {
                                        $player->sendMessage(ModuleCategory::convertToCategoryName($module->getCategory()) . " §r§7➾ §r§fНеверный синтаксис команды! Подробнее в §a.help");
                                        $this->proxy->getFFIWrapper()->getLibrary()->cancelPacket();
                                        return;
                                    }
                                    $parameters[] = (int)$args[$id + 1];
                                }
                            }
                        }
                    }

                    $module->setEnabled(null, $parameters);
                    $this->proxy->getFFIWrapper()->getLibrary()->cancelPacket();
                    return;
                }
            }
            if(!$isFinded){
                $this->proxy->getFFIWrapper()->getLibrary()->cancelPacket();
                $player->sendMessage(ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) . " §r§7➾ §r§fТакой команды не существует! Подробнее в §a.help");
            }
        }

        if($packet instanceof TransferPacket){
            $this->proxy->getLogger()->message("Игрок переносится на другой сервер: сам");

            $this->proxy->getServerEntities()->clearEntities();
            $player->getInventory()->items = [];
        }
        if($packet instanceof MovePlayerPacket){
            $player->position = new Vector3($packet->x, $packet->y, $packet->z);
            $player->yaw = $packet->yaw;
            $player->pitch = $packet->pitch;
        }
        if($packet instanceof SetEntityMotionPacket) {
            if($packet->eid === $player->eid){ // проверка вообще не нужна, но ладно
                $player->motionX = $packet->motionX;
                $player->motionY = $packet->motionY;
                $player->motionZ = $packet->motionZ;
            }
        }

        if($packet instanceof ContainerSetContentPacket) {
            if($packet->targetEid === $player->eid){
                foreach($packet->slots as $slot => $item){
                    if($item instanceof Item){
                        $player->getInventory()->items[$slot] = $item;
                    }
                }
                foreach($packet->hotbar as $slot => $item){
                    if($item instanceof Item){
                        $player->getInventory()->items[$slot] = $item;
                    }
                }
            }
        }
        if($packet instanceof ContainerOpenPacket){
            $player->currentWindowId = $packet->windowid;
        }

        if($packet instanceof PlayerActionPacket){
            switch($packet->action){
                case PlayerActionPacket::ACTION_START_SNEAK:
                    $player->setSneaking(true, false);
                    break;
                case PlayerActionPacket::ACTION_STOP_SNEAK:
                    $player->setSneaking(false, false);
                    break;
                case PlayerActionPacket::ACTION_START_SPRINT:
                    $player->setSprinting(true, false);
                    break;
                case PlayerActionPacket::ACTION_STOP_SPRINT:
                    $player->setSprinting(false, false);
                    break;
            }
        }

        if($packet instanceof UseItemPacket){

        }

    }
}
