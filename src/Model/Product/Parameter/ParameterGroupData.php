<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

class ParameterGroupData
{
    /**
     * @var string
     */
    public $akeneoCode;

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
