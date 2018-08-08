<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

interface BrandFactoryInterface
{

    public function create(BrandData $data): Brand;
}
