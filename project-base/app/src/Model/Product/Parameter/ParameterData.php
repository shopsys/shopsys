<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData as BaseParameterData;

/**
 * @property \App\Model\Product\Unit\Unit|null $unit
 */
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

    public function __construct()
    {
        // parent::__construct not called intentionally to avoid setting parameter visibility
        $this->name = [];
    }
}
