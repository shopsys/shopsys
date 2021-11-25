<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Model\Product\Unit\Unit;
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

    /**
     * @var string
     */
    public $parameterType;

    /**
     * @var \App\Model\Product\Unit\Unit|null
     */
    public ?Unit $unit = null;

    public function __construct()
    {
        // parent::__construct not called intentionally to avoid setting parameter visibility
        $this->name = [];
    }
}
