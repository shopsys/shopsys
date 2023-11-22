<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ProductAvailabilityRecalculator
{
    protected const BATCH_SIZE = 100;

    /**
     * @var \Doctrine\ORM\Internal\Hydration\IterableResult|\Shopsys\FrameworkBundle\Model\Product\Product[][]|null
     */
    protected IterableResult|array|null $productRowsIterator = null;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation $productAvailabilityCalculation
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        protected readonly ProductAvailabilityCalculation $productAvailabilityCalculation,
    ) {
    }

    public function runAllScheduledRecalculations(): void
    {
        $this->productRowsIterator = null;

        while ($this->runBatchOfScheduledDelayedRecalculations()) {
        }
    }

    /**
     * @return bool
     */
    public function runBatchOfScheduledDelayedRecalculations(): bool
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

    public function runImmediateRecalculations(): void
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
    protected function recalculateProductAvailability(Product $product): void
    {
        $calculatedAvailability = $this->productAvailabilityCalculation->calculateAvailability($product);
        $product->setCalculatedAvailability($calculatedAvailability);

        if ($product->isVariant()) {
            $this->recalculateProductAvailability($product->getMainVariant());
        }
        $this->em->flush();
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->runImmediateRecalculations();
        }
    }
}
