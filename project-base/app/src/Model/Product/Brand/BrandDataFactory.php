<?php

declare(strict_types=1);

namespace App\Model\Product\Brand;

use Shopsys\FrameworkBundle\Model\Product\Brand\BrandData as BaseBrandData;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandDataFactory as BaseBrandDataFactory;

/**
 * @method \App\Model\Product\Brand\BrandData create()
 * @method \App\Model\Product\Brand\BrandData createFromBrand(\App\Model\Product\Brand\Brand $brand)
 * @method fillNew(\App\Model\Product\Brand\BrandData $brandData)
 * @method fillFromBrand(\App\Model\Product\Brand\BrandData $brandData, \App\Model\Product\Brand\Brand $brand)
 * @method __construct(\App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory)
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
