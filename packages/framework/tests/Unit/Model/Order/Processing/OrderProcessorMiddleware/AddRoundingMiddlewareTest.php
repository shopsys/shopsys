<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddRoundingMiddleware;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Tests\FrameworkBundle\Test\IsPriceEqual;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class AddRoundingMiddlewareTest extends MiddlewareTestCase
{
    use SetTranslatorTrait;

    /**
     * @dataProvider noRoundingAddedProvider
     * @param bool $czkRounding
     * @param string $currencyCode
     */
    public function testNoRoundingIsAdded(
        bool $czkRounding,
        string $currencyCode,
    ): void {
        $expectedPrice = new Price(
            Money::create('100.52'),
            Money::create('121.63'),
        );

        $orderProcessingData = $this->createOrderProcessingData();
        $orderProcessingData->orderData->totalPrice = $expectedPrice;

        $paymentData = new PaymentData();
        $paymentData->name = ['en' => 'payment'];
        $paymentData->enabled = [1 => true];
        $paymentData->vatsIndexedByDomainId = [1 => $this->createVat()];
        $paymentData->czkRounding = $czkRounding;
        $payment = new Payment($paymentData);

        $orderProcessingData->orderData->payment = $payment;

        $addRoundingMiddleware = $this->createAddRoundingMiddleware($currencyCode);

        $result = $addRoundingMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $actualRoundingItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING);

        $this->assertCount(0, $actualRoundingItemsType);

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($expectedPrice),
        );
    }

    /**
     * @return iterable
     */
    public function noRoundingAddedProvider(): iterable
    {
        yield 'CZK currency without CZK rounding' => [false, Currency::CODE_CZK];

        yield 'Non-CZK currency with CZK rounding' => [true, Currency::CODE_EUR];

        yield 'Non-CZK currency without CZK rounding' => [false, Currency::CODE_EUR];
    }

    public function testNoRoundingIsAddedWithoutPayment(): void
    {
        $expectedPrice = new Price(
            Money::create('100.52'),
            Money::create('121.63'),
        );

        $orderProcessingData = $this->createOrderProcessingData();
        $orderProcessingData->orderData->totalPrice = $expectedPrice;

        $addRoundingMiddleware = $this->createAddRoundingMiddleware();

        $result = $addRoundingMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $actualRoundingItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING);

        $this->assertCount(0, $actualRoundingItemsType);

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($expectedPrice),
        );
    }

    /**
     * @dataProvider roundingProvider
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $inputPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $roundingPrice
     * @param int $expectedRoundingItemsCount
     */
    public function testProperRoundingIsAdded(
        Price $inputPrice,
        Price $roundingPrice,
        int $expectedRoundingItemsCount,
    ): void {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $orderProcessingData->orderData->totalPrice = $inputPrice;

        $paymentData = new PaymentData();
        $paymentData->name = ['en' => 'payment'];
        $paymentData->enabled = [1 => true];
        $paymentData->vatsIndexedByDomainId = [1 => $this->createVat()];
        $paymentData->czkRounding = true;
        $payment = new Payment($paymentData);

        $orderProcessingData->orderData->payment = $payment;

        $addRoundingMiddleware = $this->createAddRoundingMiddleware(Currency::CODE_CZK);

        $result = $addRoundingMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $actualRoundingItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING);

        $this->assertCount($expectedRoundingItemsCount, $actualRoundingItemsType);
        $this->assertCount($expectedRoundingItemsCount, $actualOrderData->items);

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($inputPrice->add($roundingPrice)),
        );

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_ROUNDING]),
            new IsPriceEqual($roundingPrice),
        );
    }

    /**
     * @return iterable
     */
    public function roundingProvider(): iterable
    {
        yield 'added rounding' => [
            new Price(Money::create('100.89'), Money::create('121.63')),
            new Price(Money::create('0.37'), Money::create('0.37')),
            1,
        ];

        yield 'no rounding for already rounded' => [
            new Price(Money::create('100'), Money::create('121')),
            Price::zero(),
            0,
        ];
    }

    /**
     * @param string $currencyCode
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddRoundingMiddleware
     */
    private function createAddRoundingMiddleware(
        string $currencyCode = Currency::CODE_EUR,
    ): AddRoundingMiddleware {
        return new AddRoundingMiddleware(
            $this->createCurrencyFacade($currencyCode),
            new Rounding(),
            $this->createOrderItemDataFactory(),
        );
    }
}
