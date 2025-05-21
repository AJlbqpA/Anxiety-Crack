<?php

class DeviceUtil
{

    public static function convertIdToString(int $os): string
    {
        return match($os){
            0 => "Android",
            1 => "iOS",
            7 => "Windows",
            default => "Undefined",
        };
    }

    public static function convertOSToInputMode(int $os): int
    {
        return match($os){
            7 => 1,
            default => 2,
        };
    }

    public static function convertInputModeToString(int $mode): string
    {
        return match($mode){
            2 => "TouchScreen",
            1 => "Mouse & Keyboard",
            4 => "Motion Controller",
            3 => "GamePad",
            default => "undefined",
        };
    }

}