<?php

declare(strict_types=1);

use pocketmine\level\format\Chunk;
use pocketmine\level\format\SubChunk;
use pocketmine\utils\BinaryStream;

final class ChunkDeserializer{
    
    private function __construct(){
        //NOOP
    }
    
    public static function networkDeserialize(
        int $chunkX, 
        int $chunkZ, 
        string $serializedData
    ) : Chunk{
        try{
            $stream = new BinaryStream($serializedData);

            $subChunks = [];
            $subChunkCount = $stream->getByte();
            for($y = 0; $y < $subChunkCount; ++$y){
                $stream->getByte(); //subchunk version, always zero
                $subChunks[$y] =
                    new SubChunk(
                        $stream->get(4096),
                        $stream->get(2048),
                        $stream->get(2048),
                        $stream->get(2048)
                    );
            }

            $heightMap = unpack("v*", $stream->get(512));
            $biomeIds = $stream->get(256);
            $stream->getByte(); //useless

            $extraData = [];
            $extraDataCount = $stream->getVarInt();
            for($i = 0; $i < $extraDataCount; ++$i){
                $extraData[$i] = [$stream->getVarInt() => $stream->getLShort()];
            }

            //$stream->getRemaining(); //tile tags (useless for restoring chunks?)


            $chunk = new Chunk($chunkX, $chunkZ, $subChunks, [], [], $biomeIds, $heightMap);

            //crutches
            $extraDataArray = new ReflectionProperty(Chunk::class, "extraData");
            $extraDataArray->setAccessible(true);
            $extraDataArray->setValue($chunk, $extraDataArray);

            $chunk->setGenerated(true);
            $chunk->setPopulated(true);
            $chunk->setLightPopulated(true);
            $chunk->setChanged(false);

            return $chunk;
        }catch(Throwable $throwable){
            return new Chunk(0, 0, [], [], []);
        }
    }
}