<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddRoundingMiddleware;
use Shopsys\FrameworkBundle\Model\Payment\OrderRoundingTypeEnum;
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

    public function testNoRoundingIsAddedForPaymentWithoutOrderRounding(): void
    {
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
        $payment = new Payment($paymentData);

        $orderItemData = new OrderItemData();
        $orderItemData->payment = $payment;
        $orderProcessingData->orderData->orderPayment = $orderItemData;

        $addRoundingMiddleware = $this->createAddRoundingMiddleware();

        $result = $addRoundingMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertCount(0, $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING));
        $this->assertThat($actualOrderData->totalPrice, new IsPriceEqual($expectedPrice));
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

        $this->assertCount(0, $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING));
        $this->assertThat($actualOrderData->totalPrice, new IsPriceEqual($expectedPrice));
    }

    #[DataProvider('roundingProvider')]
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
        $paymentData->orderRoundingTypeByDomainId = [1 => OrderRoundingTypeEnum::WHOLE];
        $payment = new Payment($paymentData);

        $orderItemData = new OrderItemData();
        $orderItemData->payment = $payment;
        $orderProcessingData->orderData->orderPayment = $orderItemData;

        $addRoundingMiddleware = $this->createAddRoundingMiddleware();

        $result = $addRoundingMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertCount($expectedRoundingItemsCount, $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING));
        $this->assertCount($expectedRoundingItemsCount, $actualOrderData->items);
        $this->assertThat($actualOrderData->totalPrice, new IsPriceEqual($inputPrice->add($roundingPrice)));
        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_ROUNDING]),
            new IsPriceEqual($roundingPrice),
        );
    }

    public static function roundingProvider(): iterable
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

    public function testEurFiveCentsRoundingIsAdded(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();
        $inputPrice = new Price(Money::create('100'), Money::create('120.12'));
        $orderProcessingData->orderData->totalPrice = $inputPrice;

        $paymentData = new PaymentData();
        $paymentData->name = ['en' => 'payment'];
        $paymentData->enabled = [1 => true];
        $paymentData->vatsIndexedByDomainId = [1 => $this->createVat()];
        $paymentData->orderRoundingTypeByDomainId = [1 => OrderRoundingTypeEnum::FIVE_CENTS];
        $payment = new Payment($paymentData);

        $orderItemData = new OrderItemData();
        $orderItemData->payment = $payment;
        $orderProcessingData->orderData->orderPayment = $orderItemData;

        $addRoundingMiddleware = $this->createAddRoundingMiddleware(
            Currency::CODE_EUR,
            Currency::ROUNDING_TYPE_HUNDREDTHS,
        );

        $result = $addRoundingMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertCount(1, $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING));

        $expectedRoundingPrice = new Price(Money::create('-0.02'), Money::create('-0.02'));
        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_ROUNDING]),
            new IsPriceEqual($expectedRoundingPrice),
        );
    }

    private function createAddRoundingMiddleware(
        string $currencyCode = Currency::CODE_EUR,
        string $roundingType = Currency::ROUNDING_TYPE_HUNDREDTHS,
    ): AddRoundingMiddleware {
        $orderItemPriceCalculationStub = $this->createStub(OrderItemPriceCalculation::class);
        $priceCalculation = new OrderPriceCalculation($orderItemPriceCalculationStub, new Rounding(), new OrderRoundingTypeEnum());

        return new AddRoundingMiddleware(
            $this->createCurrencyFacade($currencyCode, $roundingType),
            new Rounding(),
            $this->createOrderItemDataFactory(),
            $priceCalculation,
        );
    }
}
