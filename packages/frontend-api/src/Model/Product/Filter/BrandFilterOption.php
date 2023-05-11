<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class BrandFilterOption
{
    public Brand $brand;

    public int $count;

    public bool $isAbsolute;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param int $count
     * @param bool $isAbsolute
     */
    public function __construct(Brand $brand, int $count, bool $isAbsolute)
    {
        $this->brand = $brand;
        $this->count = $count;
        $this->isAbsolute = $isAbsolute;
    }
}
