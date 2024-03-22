<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStackInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class AddPaymentMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStackInterface $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    public function handle(OrderProcessingData $orderProcessingData, OrderProcessingStackInterface $orderProcessingStack): OrderProcessingData
    {
        $payment = $orderProcessingData->cart->getPayment();

        if ($payment === null) {
            return $orderProcessingStack->next()->handle($orderProcessingData, $orderProcessingStack);
        }

        $domainId = $orderProcessingData->domainConfig->getId();

        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $orderProcessingData->orderData->totalPriceByItemType[OrderItem::TYPE_PRODUCT],
            $domainId,
        );

        $orderProcessingData->orderData->totalPriceByItemType[OrderItem::TYPE_PAYMENT] = $orderProcessingData->orderData->totalPriceByItemType[OrderItem::TYPE_PAYMENT]->add($paymentPrice);
        $orderProcessingData->orderData->totalPrice = $orderProcessingData->orderData->totalPrice->add($paymentPrice);

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->priceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $paymentPrice->getPriceWithVat();
        $orderItemData->totalPriceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderItemData->totalPriceWithVat = $paymentPrice->getPriceWithVat();
        // @todo what about vat percent? $orderItemData->vatPercent = $paymentPrice->
        $orderItemData->name = $payment->getName($orderProcessingData->domainConfig->getLocale());
        $orderItemData->quantity = 1;

        $orderProcessingData->orderData->orderPayment = $orderItemData;
        $orderProcessingData->orderData->payment = $payment;

        return $orderProcessingStack->next()->handle($orderProcessingData, $orderProcessingStack);
    }
}
