<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class AddPaymentMiddleware implements OrderProcessorMiddlewareInterface
{
    public const string ADDITIONAL_DATA_GOPAY_BANK_SWIFT = 'goPayBankSwift';

    public function __construct(
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    #[Override]
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
            $orderProcessingData->orderData->freeTransportAndPaymentApplied,
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

    protected function createPaymentItemData(
        PriceInterface $paymentPrice,
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
