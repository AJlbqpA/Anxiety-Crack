<?php

use pocketmine\item\Item;

require_once("./entity/client/ClientPlayerEntity.php");

abstract class IInventory
{

    public array $items = [];
    protected ClientPlayerEntity $owner;

    public function __construct(ClientPlayerEntity $owner)
    {
        $this->owner = $owner;
    }

}