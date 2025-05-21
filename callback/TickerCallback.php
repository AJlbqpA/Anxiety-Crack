<?php

use pocketmine\network\mcpe\protocol\TextPacket;

require_once("./proxy/Proxy.php");
require_once("./entity/Entity.php");
require_once("./module/ModuleCategory.php");
require_once("./module/implements/combat/AntiBotModule.php");
require_once("./module/implements/combat/KillAuraModule.php");

class TickerCallback
{
    private Proxy $proxy;

    private const HITBOX_UPDATE_INTERVAL = 10;
    private const ENTITY_RESET_INTERVAL = 20;

    private array $ticks = [
        "hitboxUpdate" => 0,
        "targetMessage" => 0,
        "killAuraTick" => 0,
        "resetEntities" => 0,
    ];

    public ?Entity $target = null;
    private float $lastAttackTime = 0;

    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
        $this->setupTicker();
    }

    private function setupTicker(): void
    {
        $player = $this->proxy->getServerEntities()->getClientPlayerEntity();
        $antiBot = $this->proxy->getModuleProvider()->getModule("AntiBot");
        $killAura = $this->proxy->getModuleProvider()->getModule("KillAura");

        if(!$antiBot instanceof AntiBotModule || !$killAura instanceof KillAuraModule) {
            return;
        }

        $this->proxy->getFFIWrapper()->getLibrary()->setTicker(1, function() use ($player, $antiBot, $killAura) {
            $this->updateTickCounters();

            if($this->ticks["hitboxUpdate"] >= self::HITBOX_UPDATE_INTERVAL) {
                $this->ticks["hitboxUpdate"] = 0;
                $this->proxy->getModuleProvider()->getModule("HitBox")->updateHitboxes();
            }

            if($this->ticks["resetEntities"] >= self::ENTITY_RESET_INTERVAL) {
                $this->ticks["resetEntities"] = 0;
                $this->cleanupEntities();
            }

            $this->findNewTarget($player, $antiBot);

            $this->handleKillAura($player, $killAura);
        });
    }

    private function updateTickCounters(): void
    {
        foreach($this->ticks as &$tick) {
            $tick++;
        }
        unset($tick);
    }

    private function cleanupEntities(): void
    {
        $entities = $this->proxy->getServerEntities()->getEntities();
        $playerPos = $this->proxy->getServerEntities()->getClientPlayerEntity()->getPosition();

        foreach($entities as $eid => $entity) {
            if($entity->getPosition()->distance($playerPos) > 50) {
                $this->proxy->getServerEntities()->removeEntity($eid);
            }
        }

        if($this->target && !isset($entities[$this->target->eid])) {
            $this->target = null;
        }
    }

    private function findNewTarget(ClientPlayerEntity $player, AntiBotModule $antiBot): void
    {
        if($this->target !== null && $this->target->getPosition()->distance($player->getPosition()) <= 3) {
            return;
        }

        $this->target = null;

        if($antiBot->bypass === "vanilla" || !$antiBot->isEnabled()) {
            $nearestDistance = PHP_FLOAT_MAX;

            foreach($this->proxy->getServerEntities()->getEntities() as $entity) {
                if($entity->eid === $player->eid || $entity->isBot()) {
                    continue;
                }

                $distance = $entity->getPosition()->distance($player->getPosition());

                if($distance <= 3 && $distance < $nearestDistance) {
                    $this->target = $entity;
                    $nearestDistance = $distance;
                }
            }
        }
    }

    private function handleKillAura(ClientPlayerEntity $player, KillAuraModule $killAura): void
    {
        if($this->target === null || !$killAura->isEnabled())
            return;

        if($this->target->isBot())
            return;

        $currentTime = microtime(true);
        $attackInterval = 1.0 / $killAura->cps;

        if($currentTime - $this->lastAttackTime >= $attackInterval) {
            $this->lastAttackTime = $currentTime;

            $player->attackEntity($this->target->eid);

            if($this->ticks["targetMessage"] >= 20) {
                $this->ticks["targetMessage"] = 0;
                $player->sendMessage(
                    ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) .
                    " §r§7➾ §fТекущая цель: §4" . $this->target->username,
                    TextPacket::TYPE_TIP
                );
            }
        }
    }
}