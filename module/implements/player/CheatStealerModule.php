<?php

use pocketmine\block\Block;
use pocketmine\item\Food;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\TextPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");
require_once("./module/parameters/IntParameter.php");
require_once("./module/parameters/StringParameter.php");

class CheatStealerModule extends IModule
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "ChestStealer",
            "csteal",
            ModuleCategory::PLAYER,
            "Автоматически §aЗАБИРАЕТ§f вещи из §aСУНДУКА"
        );
    }

    public function onEnable(array $sendParameters = []): void
    {
    }

    public function onDisable(array $sendParameters = []): void
    {
    }

    public function onServerPacketReceive(DataPacket $packet): void
    {
        $player = $this->getClientPlayer();

        if ($packet instanceof ContainerSetContentPacket) {
            $player->currentWindowId = $packet->windowid;
            if ($packet->windowid !== 0) {
                $priorities = [
                    'sword' => 1,
                    'blocks' => 2,
                    'food' => 3,
                    'armor' => 4,
                    'default' => 5,
                ];

                $itemsWithPriority = [];

                foreach ($packet->slots as $slot => $item) {
                    if ($item->getId() !== ItemIds::AIR) {
                        $itemType = $this->getItemType($item);
                        $priority = $priorities[$itemType] ?? $priorities['default'];

                        $itemsWithPriority[] = [
                            'slot' => $slot,
                            'item' => $item,
                            'priority' => $priority,
                        ];
                    }
                }

                usort($itemsWithPriority, function ($a, $b) {
                    return $a['priority'] <=> $b['priority'];
                });

                foreach ($itemsWithPriority as $entry) {
                    $slot = $entry['slot'];
                    $item = $entry['item'];

                    $slotInventory = $player->getInventory()->findSlot();
                    if ($slotInventory === null) {
                        $player->sendMessage(ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) . " §r§7➾ §fИнвентарь полон!", TextPacket::TYPE_POPUP);
                        $player->closeWindow($packet->windowid);
                        return;
                    }

                    $player->getInventory()->setItem($slot, Item::get(ItemIds::AIR), $packet->windowid);
                    $player->getInventory()->setItem($slotInventory, $item);
                }

                $player->sendMessage(ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) . " §r§7➾ §fСундук залутан!", TextPacket::TYPE_POPUP);
                $player->closeWindow($packet->windowid);
            }
        }
    }

    private function getItemType(Item $item): string {
        if ($item->isSword()) {
            return 'sword';
        } elseif ($item instanceof Food) {
            return 'food';
        } elseif ($item->isArmor()) {
            return 'armor';
        } elseif ($item instanceof Block) {
            return 'blocks';
        }

        return 'default';
    }

}