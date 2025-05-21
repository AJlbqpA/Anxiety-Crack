<?php

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;

require_once("./inventory/IInventory.php");
require_once("./entity/client/ClientPlayerEntity.php");

class ClientPlayerInventory extends IInventory
{

    public int $currentSlot = 0;

    public function __construct(ClientPlayerEntity $owner)
    {
        parent::__construct($owner);

        $this->items = [];
        for ($i = 0; $i <= 35; $i++) {
            $this->items[$i] = Item::get(0); // Air
        }

    }

    public function addItem(Item $item): int
    {
        $slot = $this->findSlot();

        if($slot === null) return -1;
        $this->setItem($slot, $item);

        return $slot;
    }

    public function setItem(?int $slot, Item $item, int $windowId = 0): void
    {
        $packet = new ContainerSetSlotPacket();
        $packet->windowid = $windowId;
        $packet->item = $item;
        $slot = is_null($slot) ? $this->findSlot() : $slot;
        if($slot == null) return;
        $packet->slot = $slot;
        $this->owner->dataPacketToServer($packet);
        $this->owner->dataPacket($packet);
    }

    public function selectSlot(int $slot): void
    {
        $packet = new MobEquipmentPacket();
        $packet->eid = $this->owner->getEID();
        $packet->item = $this->items[$slot];
        $packet->windowId = 0;
        $packet->slot = $slot + 9;
        $packet->selectedSlot = $slot + 9;
        $this->owner->dataPacket($packet);
        $this->owner->dataPacketToServer($packet);

        $this->currentSlot = $slot;
    }

    public function findSlot() : ?int
    {
        if(count($this->items) <= 0){
            for($i = 0; $i <= 35; $i++) {
                $this->items[$i] = Item::get(0);
            }
        }
        foreach($this->items as $slot => $item) {
            if($item->getId() === 0) return $slot;
        }
        return null;
    }

    public function findItem(Item $neededItem): ?int
    {
        foreach($this->items as $slot => $item){
            if($item === $neededItem) return $slot;
        }
        return null;
    }

    public function findItemById(int $id): ?int
    {
        foreach($this->items as $slot => $item){
            if($item instanceof Item){
                if($item->getId() === $id) return $slot;
            }
        }
        return null;
    }

    public function getCurrentSlot(): int
    {
        return $this->currentSlot;
    }

    public function getItem(int $slot): Item
    {
        return $this->items[$slot] ?? Item::get(0);
    }

}