<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;

class SetCustomerUserMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
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
