<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class BrandFilterOption
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param int $count
     * @param bool $isAbsolute
     */
    public function __construct(public readonly Brand $brand, public readonly int $count, public readonly bool $isAbsolute)
    {
    }
}
