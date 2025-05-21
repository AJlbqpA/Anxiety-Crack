<?php

abstract class IParameter
{

    public string $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

}