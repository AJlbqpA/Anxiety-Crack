<?php

require_once("./module/parameters/IParameter.php");

class IntParameter extends IParameter
{

    public float $min;
    public float $max;

    public function __construct(string $description, float $min = 0, float $max = PHP_INT_MAX)
    {
        $this->min = $min;
        $this->max = $max;

        parent::__construct($description);
    }

    public function isCorrect(mixed $value): bool
    {
        $value = (float) $value;
        if(!is_float($value)) return false;

        return $value >= $this->min && $value <= $this->max;
    }

}