<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Money\Money;

class ProductFilterOptions
{
    public Money $minimalPrice;

    public Money $maximalPrice;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\Filter\BrandFilterOption[]
     */
    public array $brands;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\Filter\FlagFilterOption[]
     */
    public array $flags;

    public int $inStock;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\Filter\ParameterFilterOption[]
     */
    public array $parameters;

    public function __construct()
    {
        $this->minimalPrice = Money::zero();
        $this->maximalPrice = Money::zero();
        $this->inStock = 0;
    }
}
