<?php

declare(strict_types=1);

use pocketmine\level\format\io\region\PMAnvil;
use pocketmine\level\generator\normal\Normal;

;
require_once("./util/chunkrestorer/ChunkDeserializer.php");

class ChunkRestorer
{

    public function ConvertChunksToLevel(string $chunksPath, string $toPath): void
    {
        PMAnvil::generate($toPath, "world", 0, Normal::NAME);
        $provider = new PMAnvil($toPath);

        foreach(array_filter(scandir($chunksPath), function(string $file) : bool{
            return $file !== "." && $file !== "..";
        }) as $file){
            list($chunkX, $chunkZ) = explode("_", $file);
            $provider->saveChunk(ChunkDeserializer::networkDeserialize((int)$chunkX, (int)$chunkZ, file_get_contents($chunksPath.$file)));
        }

        $this->deleteTemp($chunksPath);
    }

    private function deleteTemp(string $dir) {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->deleteTemp($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

}