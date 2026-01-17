<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;

class SetCustomerUserMiddleware implements OrderProcessorMiddlewareInterface
{
    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderData = $orderProcessingData->orderData;
        $customerUser = $orderProcessingData->orderInput->getCustomerUser();
        $orderData->customerUser = $customerUser;

        if ($customerUser !== null) {
            $orderData->email = $customerUser->getEmail();
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}
