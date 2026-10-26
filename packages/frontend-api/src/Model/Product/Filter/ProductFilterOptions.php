<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\Category;

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

    public int $visibleFilterParametersCount = Category::DEFAULT_VISIBLE_FILTER_PARAMETERS_COUNT;

    public function __construct()
    {
        $this->minimalPrice = Money::zero();
        $this->maximalPrice = Money::zero();
        $this->inStock = 0;
    }
}
