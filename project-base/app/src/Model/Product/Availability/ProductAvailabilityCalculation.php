<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation as BaseProductAvailabilityCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Availability\Availability calculateMainVariantAvailability(\App\Model\Product\Product $mainVariant)
 * @method \App\Model\Product\Product[] getAtLeastSomewhereSellableVariantsByMainVariant(\App\Model\Product\Product $mainVariant)
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method __construct(\App\Model\Product\Availability\AvailabilityFacade $availabilityFacade, \App\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator, \App\Model\Product\ProductVisibilityFacade $productVisibilityFacade, \Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\ProductRepository $productRepository)
 * @property \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
 * @method \Shopsys\FrameworkBundle\Model\Product\Availability\Availability calculateAvailabilityForUsingStockProduct(\App\Model\Product\Product $product)
 */
class ProductAvailabilityCalculation extends BaseProductAvailabilityCalculation
{
    /**
     * @deprecated Calculated availability always return default availability
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function calculateAvailability(Product $product): Availability
    {
        return $this->availabilityFacade->getDefaultInStockAvailability();
    }
}
