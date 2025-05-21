<?php

final class Logger
{

    public function message(string $message): void
    {
        echo(date('Y/m/d H:i:s') . " " . $message . "\n");
    }

}