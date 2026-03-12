<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Item;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFactory;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class OrderItemPriceCalculationTest extends TestCase
{
    public function testCalculatePriceWithoutVat(): void
    {
        $priceCalculationMock = $this->getMockBuilder(PriceCalculation::class)
            ->onlyMethods(['getVatAmountByPriceWithVat'])
            ->disableOriginalConstructor()
            ->getMock();
        $priceCalculationMock->expects($this->once())->method('getVatAmountByPriceWithVat')->willReturn(
            Money::create(100),
        );
        $pricingSettingStub = $this->createStub(PricingSetting::class);

        $orderItemData = new OrderItemData();
        $orderItemData->unitPriceWithVat = Money::create(1000);
        $orderItemData->vatPercent = '10';

        $orderItemPriceCalculation = new OrderItemPriceCalculation(
            $priceCalculationMock,
            new VatFactory(new EntityNameResolver([])),
            new VatDataFactory(),
            $pricingSettingStub,
            new Rounding(),
        );
        $priceWithoutVat = $orderItemPriceCalculation->calculatePriceWithoutVatForInputPriceWithVat(
            $orderItemData,
            Domain::FIRST_DOMAIN_ID,
            Currency::DEFAULT_ROUNDING_PLACES_PRICE_WITHOUT_VAT,
        );

        $this->assertThat($priceWithoutVat, new IsMoneyEqual(Money::create(900)));
    }

    public function testCalculateTotalPrice(): void
    {
        $priceCalculationMock = $this->getMockBuilder(PriceCalculation::class)
            ->onlyMethods(['getVatAmountByPriceWithVat'])
            ->disableOriginalConstructor()
            ->getMock();
        $priceCalculationMock->expects($this->once())->method('getVatAmountByPriceWithVat')->willReturn(
            Money::create(10),
        );
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getInputPriceType'])
            ->getMock();
        $pricingSettingMock->expects($this->once())->method('getInputPriceType')->willReturn(
            PricingSetting::PRICE_TYPE_WITH_VAT,
        );

        $orderItemPriceCalculation = new OrderItemPriceCalculation(
            $priceCalculationMock,
            new VatFactory(new EntityNameResolver([])),
            new VatDataFactory(),
            $pricingSettingMock,
            new Rounding(),
        );

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDomainId', 'getCurrencyRoundingType', 'getCurrencyRoundingPlacesPriceWithoutVat'])
            ->getMock();
        $order->expects($this->once())->method('getDomainId')->willReturn(Domain::FIRST_DOMAIN_ID);
        $order->expects($this->once())->method('getCurrencyRoundingType')->willReturn(Currency::DEFAULT_ROUNDING_TYPE);
        $order->expects($this->once())->method('getCurrencyRoundingPlacesPriceWithoutVat')->willReturn(Currency::DEFAULT_ROUNDING_PLACES_PRICE_WITHOUT_VAT);

        $orderItem = $this->getMockBuilder(OrderItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUnitPriceWithVat', 'getQuantity', 'getVatPercent', 'getOrder'])
            ->getMock();

        $orderItem->expects($this->once())->method('getUnitPriceWithVat')->willReturn(Money::create(100));
        $orderItem->expects($this->once())->method('getQuantity')->willReturn(2);
        $orderItem->expects($this->once())->method('getVatPercent')->willReturn('1');
        $orderItem->expects($this->once())->method('getOrder')->willReturn($order);

        $totalPrice = $orderItemPriceCalculation->calculateTotalPrice($orderItem);

        $this->assertThat($totalPrice->getPriceWithVat(), new IsMoneyEqual(Money::create(200)));
        $this->assertThat($totalPrice->getPriceWithoutVat(), new IsMoneyEqual(Money::create(190)));
        $this->assertThat($totalPrice->getVatAmount(), new IsMoneyEqual(Money::create(10)));
    }
}
