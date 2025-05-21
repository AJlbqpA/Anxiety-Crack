<?php

use pocketmine\network\mcpe\protocol\DataPacket;

abstract class IModule
{

    private string $name;
    private string $command;
    private string $description;
    private string $category;

    private bool $isEnabled = false;
    private array $parameters;

    private Proxy $proxy;

    public ?int $bind = null;

    public array $lastParameters = [];

    public function __construct(Proxy $proxy, string $name, string $command, string $category, string $description, array $parameters = [])
    {
        $this->name = $name;
        $this->command = $command;
        $this->category = $category;
        $this->description = $description;

        $this->parameters = $parameters;

        $this->proxy = $proxy;
    }

    final public function hasLastParameters(): bool
    {
        return (count($this->lastParameters) >= 1);
    }

    final public function hasParameters(): bool
    {
        return (count($this->parameters) >= 1);
    }

    final public function getEntities(): array
    {
        return $this->proxy->getServerEntities()->getEntities();
    }

    public function onEnable(array $sendParameters = []): void {}
    public function onDisable(array $sendParameters = []): void {}

    public function onClientPacketSend(DataPacket $packet): void {}
    public function onServerPacketReceive(DataPacket $packet): void {}


    final public function cancelPacket(): void
    {
        $this->proxy->getFFIWrapper()->getLibrary()->cancelPacket();
    }

    final public function setEnabled(?bool $value, array $sendParameters = []): void
    {
        if(is_null($value)) $value = !$this->isEnabled();

        if($value) $this->lastParameters = $sendParameters;

        $this->isEnabled = $value;
        $this->proxy->getServerEntities()->getClientPlayerEntity()->sendMessage(
            $this->isEnabled() ?
                ModuleCategory::convertToCategoryName($this->getCategory()) . " §r§7➾ §r§fМодуль §r§a" . $this->getName() . " §r§fбыл включен!" :
                ModuleCategory::convertToCategoryName($this->getCategory()) . " §r§7➾ §r§fМодуль §r§a" . $this->getName() . " §r§fбыл выключен!"
        );
        $this->isEnabled ? $this->onEnable($sendParameters) : $this->onDisable($sendParameters);
    }

    /**
     *
     * Постоянный каллбэк пакетов (false - если не каллбэчить, при условии что модуль выключен)
     *
     * @return bool
     */
    public function alwaysPacketReceive(): bool
    {
        return false;
    }

    final public function getClientPlayer(): ClientPlayerEntity
    {
        return $this->proxy->getServerEntities()->getClientPlayerEntity();
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getDescription(): string
    {
        return $this->description;
    }

    final public function getCommand(): string
    {
        return $this->command;
    }

    final public function getCategory(): string
    {
        return $this->category;
    }

    final public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    final public function getParameters(): array
    {
        return $this->parameters;
    }

    final public function getProxy(): Proxy
    {
        return $this->proxy;
    }

}