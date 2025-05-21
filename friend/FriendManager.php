<?php

use pocketmine\utils\Config;

class FriendManager
{

    private static self $instance;
    private Config $config;

    public function __construct(string $folder)
    {
        self::$instance = $this;
        if(!(is_dir($folder))) mkdir($folder);

        $this->config = new Config($folder . "friends.json", Config::JSON);
    }

    public function addFriend(string $username): void
    {
        $friends = $this->getFriends();
        $friends[] = mb_strtolower($username);

        $this->config->set("friends", $friends);
        $this->config->save();
    }

    public function removeFriend(string $username): void
    {
        $username = mb_strtolower($username);

        $friends = $this->getFriends();
        $friends = array_filter($friends, fn(string $friend) => $friend !== $username);
        $friends = array_values($friends);

        $this->config->set("friends", $friends);
        $this->config->save();
    }

    public function isFriend(string $username): bool
    {
        return in_array(mb_strtolower($username), $this->getFriends());
    }

    public function getFriends(): array
    {
        return $this->config->get("friends") ?? [];
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

}