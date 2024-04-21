<?php

declare(strict_types=1);

namespace App\Model\Order\Processing\OrderProcessorMiddleware;

use App\Model\Security\LoginAsUserFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface;

class SetOrderDataAsAdministratorMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \App\Model\Security\LoginAsUserFacade $loginAsUserFacade
     */
    public function __construct(
        protected readonly LoginAsUserFacade $loginAsUserFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $currentAdministratorLoggedAsCustomer = $this->loginAsUserFacade->getCurrentAdministratorLoggedAsCustomer();

        if ($currentAdministratorLoggedAsCustomer === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $orderData = $orderProcessingData->orderData;

        $orderData->createdAsAdministrator = $currentAdministratorLoggedAsCustomer;
        $orderData->createdAsAdministratorName = $currentAdministratorLoggedAsCustomer->getRealName();

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}
