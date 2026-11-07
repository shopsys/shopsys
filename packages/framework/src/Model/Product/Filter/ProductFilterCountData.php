<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

class ProductFilterCountData
{
    /**
     * @var int|null
     */
    public $countInStock;

    /**
     * @var int[]
     */
    public $countByBrandId;

    /**
     * @var int[]
     */
    public $countByFlagId;

    /**
     * @var int[][]
     */
    public $countByParameterIdAndValueId;

    /**
     * @var int[]|null
     */
    public $countByParameterId;

    public function __construct()
    {
        $this->countByBrandId = [];
        $this->countByFlagId = [];
        $this->countByParameterIdAndValueId = [];
    }
}
