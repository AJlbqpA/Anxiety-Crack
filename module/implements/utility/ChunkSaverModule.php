<?php

use pocketmine\network\mcpe\protocol\FullChunkDataPacket;
use pocketmine\network\mcpe\protocol\TextPacket;

require_once("./proxy/Proxy.php");
require_once("./module/IModule.php");
require_once("./module/ModuleCategory.php");

class ChunkSaverModule extends IModule
{

    private int $chunks = 0;

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy,
            "ChunkSaver",
            "cs",
            ModuleCategory::UTILITY,
            "§aВЫКАЧИВАЕТ§f чанки и преобразовывает их в §aМИР"
        );
    }

    public function onDisable(array $sendParameters = []): void
    {
        $name = $this->getProxy()->getConfigService()->getKey("ServerAddress");
        $this->getClientPlayer()->sendMessage(ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) . " §r§7➾ §r§fМир сохранится в §r§c./chunksaver/" . $name . "/");
        $this->getProxy()->getChunkRestorer()->ConvertChunksToLevel("./chunksaver/" . $name . "/", "./chunksaver/worlds/" . $name . "/");

        $this->getProxy()->getLogger()->message("Мир " . $name . " был сохранен!");
        $this->getProxy()->getLogger()->message("Мир собран из " . $this->chunks . " чанков");

        $this->chunks = 0;
    }

    public function onServerPacketReceive(\pocketmine\network\mcpe\protocol\DataPacket $packet): void
    {
        if($packet instanceof FullChunkDataPacket){
            $dir = "./chunksaver/" . $this->getProxy()->getConfigService()->getKey("ServerAddress") . "/";
            if(!is_dir($dir)){
                mkdir($dir);
            }
            if(!is_file("./" . $packet->chunkX . "_" . $packet->chunkZ)){
                $this->chunks++;
                file_put_contents($dir . $packet->chunkX . "_" . $packet->chunkZ, $packet->data);
                $this->getClientPlayer()->sendMessage(ModuleCategory::convertToCategoryName(ModuleCategory::SELF_PROXY) . " §r§7➾ §7=> §r§fПолучен новый чанк и записан в файл", TextPacket::TYPE_POPUP);
            }
        }
    }

}