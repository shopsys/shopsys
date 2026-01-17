<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class FreeTransportAndPaymentInformationMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(protected readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade)
    {
    }

    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderProcessingData->orderData->freeTransportAndPaymentApplied = $this->freeTransportAndPaymentFacade->isFreeTransportAndPaymentApplied(
            $orderProcessingData->getDomainConfig()->getId(),
            $orderProcessingData->orderData->getProductsTotalPriceAfterAppliedDiscounts(),
            $orderProcessingData->orderInput->isFreeTransportAndPaymentPromoCodeApplied(),
        );

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}
