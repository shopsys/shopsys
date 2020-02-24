<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData as BaseParameterData;

class ParameterData extends BaseParameterData
{
    /**
     * @var \App\Model\Product\Parameter\ParameterGroup|null
     */
    public $group;

    /**
     * @var string|null
     */
    public $akeneoCode;

    /**
     * @var string|null
     */
    public $akeneoType;

    /**
     * @var int
     */
    public $orderingPriority;
}
