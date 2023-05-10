<?php

declare(strict_types=1);

namespace App\Model\Product\Availability;

use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculator as BaseProductAvailabilityRecalculator;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @property \App\Model\Product\Availability\ProductAvailabilityCalculation $productAvailabilityCalculation
 * @property \Doctrine\ORM\Internal\Hydration\IterableResult|\App\Model\Product\Product[][]|null $productRowsIterator
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler, \App\Model\Product\Availability\ProductAvailabilityCalculation $productAvailabilityCalculation)
 * @method recalculateProductAvailability(\App\Model\Product\Product $product)
 */
class ProductAvailabilityRecalculator extends BaseProductAvailabilityRecalculator
{
    /**
     * @deprecated Recalculator is disabled
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
    }

    /**
     * @return bool
     */
    public function runBatchOfScheduledDelayedRecalculations()
    {
        return false;
    }
}
