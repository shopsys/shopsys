<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddPaymentMiddleware;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Tests\FrameworkBundle\Test\IsPriceEqual;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class AddPaymentMiddlewareTest extends MiddlewareTestCase
{
    public function testPaymentIsAdded(): void
    {
        $expectedPaymentName = 'myPaymentName';
        $expectedPrice = new Price(Money::create('100'), Money::create('121'));
        $expectedBankSwift = 'CZ0820100000002201234567';

        $orderProcessingData = $this->createOrderProcessingData();

        $paymentData = new PaymentData();
        $paymentData->name = ['en' => $expectedPaymentName];
        $paymentData->enabled = [1 => true];
        $paymentData->vatsIndexedByDomainId = [1 => $this->createVat()];
        $payment = new Payment($paymentData);

        $orderProcessingData->orderInput->setPayment($payment);
        $orderProcessingData->orderInput->addAdditionalData(AddPaymentMiddleware::ADDITIONAL_DATA_GOPAY_BANK_SWIFT, $expectedBankSwift);

        $addPaymentMiddleware = $this->createAddPaymentMiddleware($expectedPrice);

        $result = $addPaymentMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertSame($actualOrderData->payment, $payment);

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_PAYMENT]),
            new IsPriceEqual($expectedPrice),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($expectedPrice),
        );

        $actualPaymentItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_PAYMENT);

        $this->assertCount(1, $actualPaymentItemsType);
        $this->assertCount(1, $actualOrderData->items);

        $this->assertSame($actualPaymentItemsType[0]->payment, $payment);
        $this->assertSame($expectedPaymentName, $actualPaymentItemsType[0]->name);
        $this->assertSame($expectedBankSwift, $actualOrderData->goPayBankSwift);
    }

    public function testPaymentIsIgnoredIfMissing(): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $addPaymentMiddleware = $this->createAddPaymentMiddleware(Price::zero());

        $result = $addPaymentMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertNull($actualOrderData->payment);
        $actualPaymentItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_PAYMENT);

        $this->assertCount(0, $actualPaymentItemsType);
        $this->assertCount(0, $actualOrderData->items);

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_PAYMENT]),
            new IsPriceEqual(Price::zero()),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual(Price::zero()),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $paymentPrice
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    private function createPaymentPriceCalculationMock(Price $paymentPrice): PaymentPriceCalculation
    {
        $paymentPriceCalculation = $this->createMock(PaymentPriceCalculation::class);
        $paymentPriceCalculation->method('calculatePrice')->willReturn($paymentPrice);

        return $paymentPriceCalculation;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $paymentPrice
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddPaymentMiddleware
     */
    private function createAddPaymentMiddleware(Price $paymentPrice): AddPaymentMiddleware
    {
        return new AddPaymentMiddleware(
            $this->createPaymentPriceCalculationMock($paymentPrice),
            $this->createCurrencyFacade(),
            $this->createOrderItemDataFactory(),
        );
    }
}
