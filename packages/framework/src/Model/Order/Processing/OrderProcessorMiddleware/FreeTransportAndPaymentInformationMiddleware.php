<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class FreeTransportAndPaymentInformationMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
    ) {
    }

    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $domainId = $orderProcessingData->getDomainConfig()->getId();
        $orderProcessingData->orderData->freeTransportAndPaymentApplied = $this->freeTransportAndPaymentFacade->isFreeTransportAndPaymentApplied(
            $domainId,
            $orderProcessingData->orderData->getProductsTotalPriceAfterAppliedDiscounts(),
            $orderProcessingData->orderInput->isFreeTransportAndPaymentPromoCodeApplied(),
            $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($domainId),
        );

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}
