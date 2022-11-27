<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Brand;

use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class BrandViewFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param string $brandMainUrl
     * @return \Shopsys\ReadModelBundle\Brand\BrandView
     */
    public function createFromBrand(Brand $brand, string $brandMainUrl): BrandView
    {
        return new BrandView(
            $brand->getId(),
            $brand->getName(),
            $brandMainUrl
        );
    }

    /**
     * @param array{brand: int|numeric-string, brand_name: string, brand_url: string} $productArray
     * @return \Shopsys\ReadModelBundle\Brand\BrandView
     */
    public function createFromProductArray(array $productArray): BrandView
    {
        return new BrandView((int)$productArray['brand'], $productArray['brand_name'], $productArray['brand_url']);
    }
}
