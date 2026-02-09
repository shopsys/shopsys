<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class BrandFilterOption
{
    public function __construct(
        public readonly Brand $brand,
        public readonly int $count,
        public readonly bool $isAbsolute,
    ) {
    }
}
