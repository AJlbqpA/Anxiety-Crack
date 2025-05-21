<?php

use pocketmine\math\Vector3;

require_once("./friend/FriendManager.php");

class Entity
{

    public string $username = "";
    public int $eid;
    public Vector3 $position;
    public Vector3 $lastPosition;
    public array $metadata = [];
    public float $lastMovementTime = 0;
    public array $movementPattern = [];
    public int $packetReceivedCount = 0;
    public float $lastPacketTime = 0;

    private const MOVEMENT_THRESHOLD = 0.001;
    private const TIME_THRESHOLD = 1.0;
    private const PACKET_RATE_LIMIT = 50;

    public function __construct(int $eid, string $username = "")
    {
        $this->username = $username;
        $this->eid = $eid;
        $this->position = new Vector3(0, 0, 0);
        $this->lastPosition = clone $this->position;
    }

    public function updatePosition(Vector3 $position): void
    {
        $this->lastPosition = clone $this->position;
        $this->position = clone $position;

        $currentTime = microtime(true);
        $this->movementPattern[] = $currentTime - $this->lastMovementTime;
        $this->lastMovementTime = $currentTime;

        if(count($this->movementPattern) > 20) {
            array_shift($this->movementPattern);
        }
    }

    public function isBot(): bool
    {
        /*if(!empty($this->metadata[\pocketmine\entity\Entity::DATA_FLAGS][\pocketmine\entity\Entity::DATA_FLAG_INVISIBLE])){
            return true;
        }*/
        if(!empty($this->metadata[\pocketmine\entity\Entity::DATA_FLAGS][\pocketmine\entity\Entity::DATA_FLAG_IMMOBILE])){
            return true;
        }

        $currentTime = microtime(true);
        if($currentTime - $this->lastPacketTime < 1.0){
            if(++$this->packetReceivedCount > self::PACKET_RATE_LIMIT){
                return true;
            }
        }else{
            $this->packetReceivedCount = 0;
            $this->lastPacketTime = $currentTime;
        }

        if(count($this->movementPattern) > 5){

            $diffX = abs($this->position->x - $this->lastPosition->x);
            $diffY = abs($this->position->y - $this->lastPosition->y);
            $diffZ = abs($this->position->z - $this->lastPosition->z);

            if($diffX > 0 && $diffX < self::MOVEMENT_THRESHOLD &&
                $diffY > 0 && $diffY < self::MOVEMENT_THRESHOLD &&
                $diffZ > 0 && $diffZ < self::MOVEMENT_THRESHOLD) {
                return true;
            }

            $stdDev = $this->calculateMovementStdDev();
            if($stdDev < 0.05){
                return true;
            }
        }

        if($this->username !== "" && $this->isSuspiciousUsername($this->username)) {
            return true;
        }

        return false;
    }

    private function calculateMovementStdDev(): float
    {
        $count = count($this->movementPattern);
        if($count < 2) return 0.0;

        $mean = array_sum($this->movementPattern) / $count;
        $sum = 0.0;

        foreach($this->movementPattern as $value) {
            $sum += pow($value - $mean, 2);
        }

        return sqrt($sum / $count);
    }

    private function isSuspiciousUsername(string $username): bool
    {
        if(preg_match('/[^\w_]/', $username)) {
            return true;
        }

        if(preg_match('/(.)\1{3,}/', $username)) {
            return true;
        }

        return false;
    }

    public function isFriend(): bool
    {
        return FriendManager::getInstance()->isFriend($this->username);
    }

    public function getPosition(): Vector3
    {
        return $this->position;
    }
}