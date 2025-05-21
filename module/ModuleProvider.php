<?php

require_once("./module/implements/combat/AntiBotModule.php");
require_once("./module/implements/combat/HitboxModule.php");
require_once("./module/implements/combat/KillAuraModule.php");
require_once("./module/implements/combat/AntiKnockBackModule.php");

require_once("./module/implements/player/FlyModule.php");
require_once("./module/implements/player/GamemodeModule.php");
require_once("./module/implements/player/LongJumpModule.php");
require_once("./module/implements/player/GlideModule.php");
require_once("./module/implements/player/JetPackModule.php");
require_once("./module/implements/player/HighJumpModule.php");
require_once("./module/implements/player/CheatStealerModule.php");
require_once("./module/implements/player/ScaffoldModule.php");
require_once("./module/implements/player/NoHungerModule.php");

require_once("./module/implements/movement/AutoSprintModule.php");

require_once("./module/implements/visuals/TimeModule.php");

require_once("./module/implements/utility/BindModule.php");
require_once("./module/implements/utility/TransferModule.php");
require_once("./module/implements/utility/ChunkSaverModule.php");
require_once("./module/implements/utility/PositionModule.php");
require_once("./module/implements/utility/DupeModule.php");
require_once("./module/implements/utility/SkinStealerModule.php");
require_once("./module/implements/utility/SoundSpammerModule.php");

class ModuleProvider
{

    private array $modules = [];

    public function __construct(Proxy $proxy)
    {
        $this->registerAll([
            new AntiBotModule($proxy),
            new HitboxModule($proxy),
            new KillAuraModule($proxy),
            new AntiKnockBackModule($proxy),

            new AutoSprintModule($proxy),

            new FlyModule($proxy),
            new GamemodeModule($proxy),
            new LongJumpModule($proxy),
            new GlideModule($proxy),
            new JetPackModule($proxy),
            new HighJumpModule($proxy),
            new CheatStealerModule($proxy),
            //new ScaffoldModule($proxy),
            new NoHungerModule($proxy),

            new TimeModule($proxy),

            new TransferModule($proxy),
            new ChunkSaverModule($proxy),
            new BindModule($proxy),
            new PositionModule($proxy),
            //new DupeModule($proxy),
            new SKinStealerModule($proxy),
            new SoundSpammerModule($proxy)
        ]);
    }

    public function registerAll(array $modules): void
    {
        foreach($modules as $module){
            if($module instanceof IModule){
                if(isset($this->modules[$module->getName()]))
                    throw new Exception("Module " . $module->getName() . " already registered!");

                $this->modules[$module->getName()] = $module;
            }
        }
    }

    public function getModule(string $name): IModule
    {
        return $this->modules[$name];
    }

    public function getModules(): array
    {
        return $this->modules;
    }

}