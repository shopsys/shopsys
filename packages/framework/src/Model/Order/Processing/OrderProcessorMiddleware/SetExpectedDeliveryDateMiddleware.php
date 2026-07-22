<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;

class SetExpectedDeliveryDateMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly TransportExpectedDeliveryDateCalculation $transportExpectedDeliveryDateCalculation,
    ) {
    }

    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderInput = $orderProcessingData->orderInput;
        $transport = $orderInput->getTransport();

        if ($transport === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $orderProcessingData->orderData->expectedDeliveryDate = $this->transportExpectedDeliveryDateCalculation
            ->calculateExpectedDeliveryDateForQuantifiedProducts(
                $transport,
                $orderInput->getQuantifiedProducts(),
                $orderProcessingData->getDomainId(),
                $orderInput->findAdditionalData(PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER),
            );

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}
