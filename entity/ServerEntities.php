<?php

require_once("./entity/client/ClientPlayerEntity.php");
require_once("./entity/Entity.php");

class ServerEntities
{
    private Proxy $proxy;
    private ClientPlayerEntity $clientPlayerEntity;
    private array $entities = [];
    private array $botList = [];

    public function __construct(Proxy $proxy)
    {
        $this->clientPlayerEntity = new ClientPlayerEntity($proxy);
        $this->proxy = $proxy;
    }

    public function getClientPlayerEntity(): ClientPlayerEntity
    {
        return $this->clientPlayerEntity;
    }

    public function addEntity(Entity $entity, array $metadata = []): void
    {
        if(!isset($this->entities[$entity->eid])){
            $this->entities[$entity->eid] = $entity;
            if(count($metadata) >= 1) {
                $this->entities[$entity->eid]->metadata = $metadata;

                if($entity->isBot()) {
                    $this->botList[$entity->eid] = true;
                    //$this->proxy->getLogger()->message("Bot detected: " . ($entity->username ?: "EID: ".$entity->eid));
                }
            }
        }
    }

    public function clearEntities(): void
    {
        $this->entities = [];
        $this->botList = [];
    }

    public function removeEntity(int $eid): void
    {
        unset($this->entities[$eid]);
        unset($this->botList[$eid]);
    }

    public function getEntity(int $eid): ?Entity
    {
        return $this->entities[$eid] ?? null;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function isBot(int $eid): bool
    {
        return isset($this->botList[$eid]) ||
            (isset($this->entities[$eid]) && $this->entities[$eid]->isBot());
    }

    public function getBots(): array
    {
        return array_filter($this->entities, fn($e) => $this->isBot($e->eid));
    }
}