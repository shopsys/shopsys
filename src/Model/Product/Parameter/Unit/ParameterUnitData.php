<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Unit;

class ParameterUnitData
{
    /**
     * @var string|null
     */
    public $unit;

    /**
     * @var string[]|null[]
     */
    public $name;

    public function __construct()
    {
        $this->name = [];
    }
}
