<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

interface BrandDataFactoryInterface
{
    public function create(): BrandData;

    public function createFromBrand(Brand $brand): BrandData;
}
