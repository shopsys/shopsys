<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\EntityManagerFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductPriceRecalculator
{
    const BATCH_SIZE = 250;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\EntityManagerFacade
     */
    private $entityManagerFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation
     */
    private $productPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPriceRepository
     */
    private $productCalculatedPriceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     */
    private $productPriceRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade
     */
    private $pricingGroupFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]|null
     */
    private $allPricingGroups;

    /**
     * @var \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]|null
     */
    private $productRowsIterator;

    public function __construct(
        EntityManagerInterface $em,
        EntityManagerFacade $entityManagerFacade,
        ProductPriceCalculation $productPriceCalculation,
        ProductCalculatedPriceRepository $productCalculatedPriceRepository,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        PricingGroupFacade $pricingGroupFacade
    ) {
        $this->em = $em;
        $this->entityManagerFacade = $entityManagerFacade;
        $this->productPriceCalculation = $productPriceCalculation;
        $this->productCalculatedPriceRepository = $productCalculatedPriceRepository;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
        $this->pricingGroupFacade = $pricingGroupFacade;
    }

    /**
     * @return bool
     */
    public function runBatchOfScheduledDelayedRecalculations()
    {
        if ($this->productRowsIterator === null) {
            $this->productRowsIterator = $this->productPriceRecalculationScheduler->getProductsIteratorForDelayedRecalculation();
        }

        for ($count = 0; $count < self::BATCH_SIZE; $count++) {
            $row = $this->productRowsIterator->next();
            if ($row === false) {
                $this->clearCache();
                $this->entityManagerFacade->clear();

                return false;
            }
            $this->recalculateProductPrices($row[0]);
        }
        $this->clearCache();
        $this->entityManagerFacade->clear();

        return true;
    }

    public function runAllScheduledRecalculations()
    {
        $this->productRowsIterator = null;
        // @codingStandardsIgnoreStart
        while ($this->runBatchOfScheduledDelayedRecalculations()) {
        }
        // @codingStandardsIgnoreEnd
    }

    private function clearCache()
    {
        $this->allPricingGroups = null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    private function getAllPricingGroups()
    {
        if ($this->allPricingGroups === null) {
            $this->allPricingGroups = $this->pricingGroupFacade->getAll();
        }

        return $this->allPricingGroups;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function recalculateProductPrices(Product $product)
    {
        foreach ($this->getAllPricingGroups() as $pricingGroup) {
            try {
                $price = $this->productPriceCalculation->calculatePrice($product, $pricingGroup->getDomainId(), $pricingGroup);
                $priceWithVat = $price->getPriceWithVat();
            } catch (\Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException $e) {
                $priceWithVat = null;
            }
            $this->productCalculatedPriceRepository->saveCalculatedPrice($product, $pricingGroup, $priceWithVat);
        }
        $product->markPriceAsRecalculated();
        $product->markProductForVisibilityRecalculation();
        $this->em->flush($product);
    }
}
