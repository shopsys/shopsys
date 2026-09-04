<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;

class ApplyGiftVouchersMiddleware implements OrderProcessorMiddlewareInterface
{
    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderProcessingData->orderData->giftVouchers = $orderProcessingData->orderInput->getGiftVouchers();

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}
