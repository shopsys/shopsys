<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class AddTransportMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
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
        $transport = $orderProcessingData->inputOrderData->getTransport();

        if ($transport === null) {
            return $orderProcessingStack->next()->handle($orderProcessingData, $orderProcessingStack);
        }

        $domainId = $orderProcessingData->domainConfig->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $transportPrice = $this->transportPriceCalculation->calculatePrice(
            $transport,
            $currency,
            $orderProcessingData->orderData->totalPriceByItemType[OrderItem::TYPE_PRODUCT],
            $domainId,
        );

        $orderItemData = $this->orderItemDataFactory->create(OrderItem::TYPE_TRANSPORT);
        $orderItemData->unitPriceWithoutVat = $transportPrice->getPriceWithoutVat();
        $orderItemData->unitPriceWithVat = $transportPrice->getPriceWithVat();
        $orderItemData->totalPriceWithoutVat = $transportPrice->getPriceWithoutVat();
        $orderItemData->totalPriceWithVat = $transportPrice->getPriceWithVat();
        $orderItemData->vatPercent = $transport->getTransportDomain($domainId)->getVat()->getPercent();
        $orderItemData->name = $transport->getName($orderProcessingData->domainConfig->getLocale());
        $orderItemData->quantity = 1;

        $orderData = $orderProcessingData->orderData;

        $orderData->addTotalPrice($transportPrice, OrderItem::TYPE_TRANSPORT);

        $orderData->orderTransport = $orderItemData;
        $orderData->transport = $transport;

        $orderData->addItem($orderItemData);

        return $orderProcessingStack->next()->handle($orderProcessingData, $orderProcessingStack);
    }
}
