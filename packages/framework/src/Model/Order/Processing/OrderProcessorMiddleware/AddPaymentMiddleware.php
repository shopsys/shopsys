<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class AddPaymentMiddleware implements OrderProcessorMiddlewareInterface
{
    public const string ADDITIONAL_DATA_GOPAY_BANK_SWIFT = 'goPayBankSwift';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
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
        $payment = $orderProcessingData->inputOrderData->getPayment();

        if ($payment === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $domainId = $orderProcessingData->domainConfig->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $orderProcessingData->orderData->totalPriceByItemType[OrderItem::TYPE_PRODUCT],
            $domainId,
        );

        $orderItemData = $this->orderItemDataFactory->create(OrderItem::TYPE_PAYMENT);
        $orderItemData->unitPriceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderItemData->unitPriceWithVat = $paymentPrice->getPriceWithVat();
        $orderItemData->totalPriceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderItemData->totalPriceWithVat = $paymentPrice->getPriceWithVat();
        $orderItemData->vatPercent = $payment->getPaymentDomain($domainId)->getVat()->getPercent();
        $orderItemData->name = $payment->getName($orderProcessingData->domainConfig->getLocale());
        $orderItemData->quantity = 1;

        $orderData = $orderProcessingData->orderData;

        $orderData->addTotalPrice($paymentPrice, OrderItem::TYPE_PAYMENT);

        $orderData->orderPayment = $orderItemData;
        $orderData->payment = $payment;
        $orderData->goPayBankSwift = $orderProcessingData->inputOrderData->findAdditionalData(static::ADDITIONAL_DATA_GOPAY_BANK_SWIFT);

        $orderData->addItem($orderItemData);

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}
