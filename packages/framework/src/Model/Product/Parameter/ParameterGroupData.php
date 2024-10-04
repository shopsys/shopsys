<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterGroupData
{
    /**
     * @var string[]|null[]
     */
    public $names;

    /**
     * @var int
     */
    public $orderingPriority;

    public function __construct()
    {
        $this->names = [];
    }
}
