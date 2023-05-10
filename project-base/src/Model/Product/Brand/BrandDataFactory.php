<?php

declare(strict_types=1);

namespace App\Model\Product\Brand;

use Shopsys\FrameworkBundle\Model\Product\Brand\BrandData as BaseBrandData;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandDataFactory as BaseBrandDataFactory;

/**
 * @method \App\Model\Product\Brand\BrandData create()
 * @method \App\Model\Product\Brand\BrandData createFromBrand(\App\Model\Product\Brand\Brand $brand)
 */
class BrandDataFactory extends BaseBrandDataFactory
{
    /**
     * @return \App\Model\Product\Brand\BrandData
     */
    protected function createInstance(): BaseBrandData
    {
        $brandData = new BrandData();
        $brandData->image = $this->imageUploadDataFactory->create();

        return $brandData;
    }
}
