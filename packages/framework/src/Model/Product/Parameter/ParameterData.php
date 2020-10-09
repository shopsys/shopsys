<?php

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

    public function __construct()
    {
        $this->name = [];
        $this->visible = false;
    }
}
