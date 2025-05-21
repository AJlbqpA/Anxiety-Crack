<?php

require_once("./module/parameters/IParameter.php");

class StringParameter extends IParameter
{

    public array $templates;

    public function __construct(string $description, array $templates = [])
    {
        $this->templates = $templates;

        parent::__construct($description);
    }

    public function isCorrect(string $value): bool
    {
        if(!(count($this->templates) > 0)) return true;
        return in_array($value, $this->templates);
    }

}