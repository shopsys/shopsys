<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterGroupData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var int
     */
    public $position;

    public function __construct()
    {
        $this->name = [];
    }
}
