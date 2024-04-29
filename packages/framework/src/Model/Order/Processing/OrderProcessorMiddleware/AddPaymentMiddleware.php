<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

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
        $payment = $orderProcessingData->orderInput->getPayment();

        if ($payment === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $domainId = $orderProcessingData->getDomainId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $orderProcessingData->orderData->getProductsTotalPriceAfterAppliedDiscounts(),
            $domainId,
        );

        $orderItemData = $this->createPaymentItemData($paymentPrice, $payment, $orderProcessingData->getDomainConfig());

        $orderData = $orderProcessingData->orderData;

        $orderData->addTotalPrice($paymentPrice, OrderItemTypeEnum::TYPE_PAYMENT);

        $orderData->orderPayment = $orderItemData;
        $orderData->payment = $payment;
        $orderData->goPayBankSwift = $orderProcessingData->orderInput->findAdditionalData(static::ADDITIONAL_DATA_GOPAY_BANK_SWIFT);

        $orderData->addItem($orderItemData);

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $paymentPrice
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData
     */
    protected function createPaymentItemData(
        Price $paymentPrice,
        Payment $payment,
        DomainConfig $domainConfig,
    ): OrderItemData {
        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PAYMENT);

        $orderItemData->name = $payment->getName($domainConfig->getLocale());
        $orderItemData->setUnitPrice($paymentPrice);
        $orderItemData->setTotalPrice($paymentPrice);
        $orderItemData->vatPercent = $payment->getPaymentDomain($domainConfig->getId())->getVat()->getPercent();
        $orderItemData->quantity = 1;
        $orderItemData->payment = $payment;

        return $orderItemData;
    }
}
