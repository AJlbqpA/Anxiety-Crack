<?php

declare(strict_types=1);

use pocketmine\block\BlockIds;
use pocketmine\level\format\Chunk;
use pocketmine\block\Block;

final class Level
{
    /** @var Chunk[][] */
    private array $chunks = [];

    /** @var Block[][][] */
    private array $blockCache = [];

    public function __construct()
    {
        // todo
    }

    public function updateChunk(int $chunkX, int $chunkZ, string $chunkData): void
    {
        $chunk = ChunkDeserializer::networkDeserialize($chunkX, $chunkZ, $chunkData);
        $this->chunks[$chunkX][$chunkZ] = $chunk;

        unset($this->blockCache[$chunkX][$chunkZ]);
    }

    public function updateBlock(int $x, int $y, int $z, int $blockId, int $blockMeta = 0): void
    {
        $chunkX = $x >> 4;
        $chunkZ = $z >> 4;

        if (!isset($this->chunks[$chunkX][$chunkZ])) {
            return;
        }

        $chunk = $this->chunks[$chunkX][$chunkZ];
        $chunk->setBlock($x & 0x0f, $y, $z & 0x0f, $blockId, $blockMeta);

        $this->blockCache[$x][$y][$z] = Block::get($blockId, $blockMeta);
    }

    public function getBlock(int $x, int $y, int $z): Block
    {
        if (isset($this->blockCache[$x][$y][$z])) {
            return $this->blockCache[$x][$y][$z];
        }

        $chunkX = $x >> 4;
        $chunkZ = $z >> 4;

        if (!isset($this->chunks[$chunkX][$chunkZ])) {
            return Block::get(BlockIds::AIR);
        }

        $chunk = $this->chunks[$chunkX][$chunkZ];

        $fullBlock = $chunk->getFullBlock($x & 0x0f, $y, $z & 0x0f);
        $blockId = $fullBlock >> 4;
        $meta = $fullBlock & 0x0f;

        $block = Block::get($blockId, $meta);
        $this->blockCache[$x][$y][$z] = $block;

        return $block;
    }
}