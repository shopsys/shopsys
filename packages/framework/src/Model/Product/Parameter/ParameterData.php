<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var bool
     */
    public $visible;

    /**
     * @var string|null
     */
    public $uuid;

    /**
     * @var int
     */
    public $orderingPriority;

    /**
     * @var string
     */
    public $parameterType;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit|null
     */
    public $unit;

    public function __construct()
    {
        $this->name = [];
        $this->visible = false;
    }
}
