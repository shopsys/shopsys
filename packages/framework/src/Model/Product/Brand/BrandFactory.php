<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

class BrandFactory implements BrandFactoryInterface
{
    public function create(BrandData $data): Brand
    {
        return new Brand($data);
    }
}
