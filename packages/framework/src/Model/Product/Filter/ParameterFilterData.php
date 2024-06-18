<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

class ParameterFilterData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter|null
     */
    public $parameter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public $values = [];

    /**
     * @var float|null
     */
    public ?float $minimalValue = null;

    /**
     * @var float|null
     */
    public ?float $maximalValue = null;
}
