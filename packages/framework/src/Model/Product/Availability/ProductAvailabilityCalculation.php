<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class ProductAvailabilityCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(
        protected readonly AvailabilityFacade $availabilityFacade,
        protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function calculateAvailability(Product $product)
    {
        // If the product is not managed by EntityManager yet, it's not possible to calculate its availability consistently
        // Let's return a same availability for the moment, do not change it now and mark the product for recalculation
        if ($this->em->contains($product) === false) {
            $product->markForAvailabilityRecalculation();

            if ($product->isUsingStock()) {
                return $this->calculateAvailabilityForUsingStockProduct($product);
            }

            return $product->getCalculatedAvailability();
        }

        if ($product->isMainVariant()) {
            return $this->calculateMainVariantAvailability($product);
        }

        if ($product->isUsingStock()) {
            return $this->calculateAvailabilityForUsingStockProduct($product);
        }

        return $product->getAvailability();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    protected function calculateAvailabilityForUsingStockProduct(Product $product): Availability
    {
        if ($product->getStockQuantity() <= 0
            && $product->getOutOfStockAction() === Product::OUT_OF_STOCK_ACTION_SET_ALTERNATE_AVAILABILITY
        ) {
            return $product->getOutOfStockAvailability();
        }

        return $this->availabilityFacade->getDefaultInStockAvailability();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainVariant
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    protected function calculateMainVariantAvailability(Product $mainVariant)
    {
        $atLeastSomewhereSellableVariants = $this->getAtLeastSomewhereSellableVariantsByMainVariant($mainVariant);

        if (count($atLeastSomewhereSellableVariants) === 0) {
            return $this->availabilityFacade->getDefaultInStockAvailability();
        }
        $fastestAvailability = $this->calculateAvailability(array_shift($atLeastSomewhereSellableVariants));

        foreach ($atLeastSomewhereSellableVariants as $variant) {
            $variantCalculatedAvailability = $this->calculateAvailability($variant);

            if ($fastestAvailability->getDispatchTime() === null
                || $variantCalculatedAvailability->getDispatchTime() !== null
                && $variantCalculatedAvailability->getDispatchTime() < $fastestAvailability->getDispatchTime()
            ) {
                $fastestAvailability = $variantCalculatedAvailability;
            }
        }

        return $fastestAvailability;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainVariant
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getAtLeastSomewhereSellableVariantsByMainVariant(Product $mainVariant)
    {
        $allVariants = $mainVariant->getVariants();

        foreach ($allVariants as $variant) {
            $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($variant);
            $variant->markForVisibilityRecalculation();
        }
        $this->em->flush();
        $this->productVisibilityFacade->refreshProductsVisibilityForMarked();

        return $this->productRepository->getAtLeastSomewhereSellableVariantsByMainVariant($mainVariant);
    }
}
