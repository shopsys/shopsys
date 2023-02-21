<?php

declare(strict_types=1);

namespace App\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandData as BaseBrandData;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandDataFactory as BaseBrandDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;

/**
 * @method \App\Model\Product\Brand\BrandData create()
 * @method \App\Model\Product\Brand\BrandData createFromBrand(\App\Model\Product\Brand\Brand $brand)
 */
class BrandDataFactory extends BaseBrandDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        FriendlyUrlFacade $friendlyUrlFacade,
        BrandFacade $brandFacade,
        Domain $domain,
        ImageUploadDataFactory $imageUploadDataFactory,
    ) {
        parent::__construct($friendlyUrlFacade, $brandFacade, $domain, $imageUploadDataFactory);
    }

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
