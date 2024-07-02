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

    public function __construct()
    {
        // parent::__construct not called intentionally to avoid setting parameter visibility
        $this->name = [];
    }
}
