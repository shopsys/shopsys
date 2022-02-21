<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ProductAvailabilityRecalculator
{
    /** @access protected */
    const BATCH_SIZE = 100;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler
     */
    protected $productAvailabilityRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation
     */
    protected $productAvailabilityCalculation;

    /**
     * @var \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]|null
     */
    protected $productRowsIterator;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation $productAvailabilityCalculation
     */
    public function __construct(
        EntityManagerInterface $em,
        ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        ProductAvailabilityCalculation $productAvailabilityCalculation
    ) {
        $this->em = $em;
        $this->productAvailabilityRecalculationScheduler = $productAvailabilityRecalculationScheduler;
        $this->productAvailabilityCalculation = $productAvailabilityCalculation;
    }

    public function runAllScheduledRecalculations()
    {
        $this->productRowsIterator = null;
        while ($this->runBatchOfScheduledDelayedRecalculations()) {
        }
    }

    /**
     * @return bool
     */
    public function runBatchOfScheduledDelayedRecalculations()
    {
        if ($this->productRowsIterator === null) {
            $this->productRowsIterator = $this->productAvailabilityRecalculationScheduler->getProductsIteratorForDelayedRecalculation();
        }

        for ($count = 0; $count < static::BATCH_SIZE; $count++) {
            $row = $this->productRowsIterator->next();
            if ($row === false) {
                $this->em->clear();

                return false;
            }
            $this->recalculateProductAvailability($row[0]);
        }

        $this->em->clear();

        return true;
    }

    public function runImmediateRecalculations()
    {
        $products = $this->productAvailabilityRecalculationScheduler->getProductsForImmediateRecalculation();
        foreach ($products as $product) {
            $this->recalculateProductAvailability($product);
        }
        $this->productAvailabilityRecalculationScheduler->cleanScheduleForImmediateRecalculation();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    protected function recalculateProductAvailability(Product $product)
    {
        $calculatedAvailability = $this->productAvailabilityCalculation->calculateAvailability($product);
        $product->setCalculatedAvailability($calculatedAvailability);
        if ($product->isVariant()) {
            $this->recalculateProductAvailability($product->getMainVariant());
        }
        $this->em->flush($product);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            $this->runImmediateRecalculations();
        }
    }
}
