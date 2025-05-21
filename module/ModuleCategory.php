<?php

class ModuleCategory
{

    public const COMBAT = "Combat";
    public const PLAYER = "Player";
    public const MOVEMENT = "Movement";
    public const VISUAL = "Visual";
    public const UTILITY = "Utility";

    public const SELF_PROXY = "Proxy";

    public static function convertToCategoryName(string $category): string
    {
        switch($category){
            case self::COMBAT:
                return "§r§cＣｏｍｂａｔ";
            case self::PLAYER:
                return "§r§aＰｌａｙｅｒ";
            case self::MOVEMENT:
                return "§r§dＭｏｖｅｍｅｎｔ";
            case self::UTILITY:
                return "§r§9Ｕｔｉｌｉｔｙ";
            case self::VISUAL:
                return "§r§bＶｉｓｕａｌｓ";
            case self::SELF_PROXY:
                return "§r§cＡｎｘｉｅｔｙ";
            default:
                return "§r§7Ｕｎｋｏｗｎ";
        }
    }

}