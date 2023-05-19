<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

interface BrandFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function create(BrandData $data): Brand;
}
