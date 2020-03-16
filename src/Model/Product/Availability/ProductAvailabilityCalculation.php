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
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade, \Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator, \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade, \Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\ProductRepository $productRepository)
 */
class ProductAvailabilityCalculation extends BaseProductAvailabilityCalculation
{
    /**
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function calculateAvailability(Product $product): Availability
    {
        return $this->availabilityFacade->getDefaultInStockAvailability();
    }
}
