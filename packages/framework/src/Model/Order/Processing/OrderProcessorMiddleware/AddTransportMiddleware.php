<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use App\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStackInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class AddTransportMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStackInterface $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    public function handle(OrderProcessingData $orderProcessingData, OrderProcessingStackInterface $orderProcessingStack,): OrderProcessingData
    {
        $transport = $orderProcessingData->cart->getTransport();

        if ($transport !== null) {
            $domainId = $orderProcessingData->domainConfig->getId();

            $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

            $transportPrice = $this->transportPriceCalculation->calculatePrice(
                $transport,
                $currency,
                $orderProcessingData->orderData->totalPriceByItemType[OrderItem::TYPE_PRODUCT],
                $domainId,
            );

            $orderProcessingData->orderData->totalPriceByItemType[OrderItem::TYPE_TRANSPORT] = $orderProcessingData->orderData->totalPriceByItemType[OrderItem::TYPE_TRANSPORT]->add($transportPrice);
            $orderProcessingData->orderData->totalPrice = $orderProcessingData->orderData->totalPrice->add($transportPrice);

            $orderItemData = $this->orderItemDataFactory->create();
            $orderItemData->priceWithoutVat = $transportPrice->getPriceWithoutVat();
            $orderItemData->priceWithVat = $transportPrice->getPriceWithVat();
            $orderItemData->totalPriceWithoutVat = $transportPrice->getPriceWithoutVat();
            $orderItemData->totalPriceWithVat = $transportPrice->getPriceWithVat();
            // @todo what about vat percent? $orderItemData->vatPercent = $transportPrice->
            $orderItemData->name = $transport->getName($orderProcessingData->domainConfig->getLocale());
            $orderItemData->quantity = 1;

            $orderProcessingData->orderData->orderTransport = $orderItemData;
        }

        return $orderProcessingStack->next()->handle($orderProcessingData, $orderProcessingStack);
    }
}
