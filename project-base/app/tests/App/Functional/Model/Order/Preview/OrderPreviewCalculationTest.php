<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Order\Preview;

use App\Model\Payment\Payment;
use App\Model\Transport\Transport;
use PHPUnit\Framework\MockObject\MockObject;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewCalculation;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Tests\App\Test\FunctionalTestCase;
use Tests\FrameworkBundle\Test\IsMoneyEqual;
use Tests\FrameworkBundle\Test\Provider\TestCurrencyProvider;

class OrderPreviewCalculationTest extends FunctionalTestCase
{
    public function testCalculatePreviewWithTransportAndPayment()
    {
        $paymentPrice = new Price(Money::create(100), Money::create(120));
        $transportPrice = new Price(Money::create(10), Money::create(12));
        $quantifiedItemsPrices = $this->createQuantifiedItemsPrices();
        $currency = TestCurrencyProvider::getTestCurrency();

        /** @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation|\PHPUnit\Framework\MockObject\MockObject $paymentPriceCalculationMock */
        $paymentPriceCalculationMock = $this->createMock(PaymentPriceCalculation::class);
        $paymentPriceCalculationMock->expects($this->once())->method('calculatePrice')->willReturn($paymentPrice);

        /** @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation|\PHPUnit\Framework\MockObject\MockObject $transportPriceCalculationMock */
        $transportPriceCalculationMock = $this->createMock(TransportPriceCalculation::class);
        $transportPriceCalculationMock->expects($this->once())->method('calculatePrice')->willReturn($transportPrice);

        /** @var \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation|\PHPUnit\Framework\MockObject\MockObject $orderPriceCalculationMock */
        $orderPriceCalculationMock = $this->createMock(OrderPriceCalculation::class);
        $orderPriceCalculationMock->expects($this->any())->method('calculateOrderRoundingPrice')->willReturn(null);

        $previewCalculation = new OrderPreviewCalculation(
            $this->createQuantifiedProductPriceCalculationMock($quantifiedItemsPrices),
            $this->createQuantifiedProductDiscountCalculationMock(),
            $transportPriceCalculationMock,
            $paymentPriceCalculationMock,
            $orderPriceCalculationMock,
        );

        $quantifiedProducts = $this->createQuantifiedProducts();

        /** @var \Shopsys\FrameworkBundle\Model\Transport\Transport|\PHPUnit\Framework\MockObject\MockObject $transport */
        $transport = $this->createMock(Transport::class);

        /** @var \Shopsys\FrameworkBundle\Model\Payment\Payment|\PHPUnit\Framework\MockObject\MockObject $payment */
        $payment = $this->createMock(Payment::class);

        $orderPreview = $previewCalculation->calculatePreview(
            $currency,
            $this->domain->getId(),
            $quantifiedProducts,
            $transport,
            $payment,
            null,
        );

        $this->assertSame($quantifiedProducts, $orderPreview->getQuantifiedProducts());
        $this->assertSame($quantifiedItemsPrices, $orderPreview->getQuantifiedItemsPrices());
        $this->assertSame($payment, $orderPreview->getPayment());
        $this->assertSame($paymentPrice, $orderPreview->getPaymentPrice());
        $this->assertThat($orderPreview->getTotalPrice()->getVatAmount(), new IsMoneyEqual(Money::create(822)));
        $this->assertThat($orderPreview->getTotalPrice()->getPriceWithVat(), new IsMoneyEqual(Money::create(4932)));
        $this->assertThat($orderPreview->getTotalPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::create(4110)));
        $this->assertSame($transport, $orderPreview->getTransport());
        $this->assertSame($transportPrice, $orderPreview->getTransportPrice());
    }

    public function testCalculatePreviewWithoutTransportAndPayment()
    {
        $quantifiedItemsPrices = $this->createQuantifiedItemsPrices();
        $currency = TestCurrencyProvider::getTestCurrency();

        /** @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation|\PHPUnit\Framework\MockObject\MockObject $paymentPriceCalculationMock */
        $paymentPriceCalculationMock = $this->createMock(PaymentPriceCalculation::class);
        $paymentPriceCalculationMock->expects($this->never())->method('calculatePrice');

        /** @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation|\PHPUnit\Framework\MockObject\MockObject $transportPriceCalculationMock */
        $transportPriceCalculationMock = $this->createMock(TransportPriceCalculation::class);
        $transportPriceCalculationMock->expects($this->never())->method('calculatePrice');

        /** @var \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation|\PHPUnit\Framework\MockObject\MockObject $orderPriceCalculationMock */
        $orderPriceCalculationMock = $this->createMock(OrderPriceCalculation::class);

        $previewCalculation = new OrderPreviewCalculation(
            $this->createQuantifiedProductPriceCalculationMock($quantifiedItemsPrices),
            $this->createQuantifiedProductDiscountCalculationMock(),
            $transportPriceCalculationMock,
            $paymentPriceCalculationMock,
            $orderPriceCalculationMock,
        );

        $quantifiedProducts = $this->createQuantifiedProducts();

        $orderPreview = $previewCalculation->calculatePreview(
            $currency,
            $this->domain->getId(),
            $quantifiedProducts,
            null,
            null,
            null,
        );

        $this->assertSame($quantifiedProducts, $orderPreview->getQuantifiedProducts());
        $this->assertSame($quantifiedItemsPrices, $orderPreview->getQuantifiedItemsPrices());
        $this->assertNull($orderPreview->getPayment());
        $this->assertNull($orderPreview->getPaymentPrice());
        $this->assertThat($orderPreview->getTotalPrice()->getVatAmount(), new IsMoneyEqual(Money::create(800)));
        $this->assertThat($orderPreview->getTotalPrice()->getPriceWithVat(), new IsMoneyEqual(Money::create(4800)));
        $this->assertThat($orderPreview->getTotalPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::create(4000)));
        $this->assertNull($orderPreview->getTransport());
        $this->assertNull($orderPreview->getTransportPrice());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    private function createVat(): Vat
    {
        $vatData = new VatData();
        $vatData->name = 'vatName';
        $vatData->percent = '20';

        return new Vat($vatData, Domain::FIRST_DOMAIN_ID);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[]
     */
    private function createQuantifiedItemsPrices(): array
    {
        $vat = $this->createVat();
        $unitPrice = new Price(Money::create(1000), Money::create(1200));
        $totalPrice = new Price(Money::create(2000), Money::create(2400));
        $quantifiedItemPrice = new QuantifiedItemPrice($unitPrice, $totalPrice, $vat);

        return [$quantifiedItemPrice, $quantifiedItemPrice];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createQuantifiedProductPriceCalculationMock(
        array $quantifiedItemsPrices,
    ): QuantifiedProductPriceCalculation&MockObject {
        $mock = $this->getMockBuilder(QuantifiedProductPriceCalculation::class)
            ->onlyMethods(['calculatePrices', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())->method('calculatePrices')->willReturn($quantifiedItemsPrices);

        return $mock;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductDiscountCalculation&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createQuantifiedProductDiscountCalculationMock(): QuantifiedProductDiscountCalculation&MockObject
    {
        $mock = $this->getMockBuilder(QuantifiedProductDiscountCalculation::class)
            ->onlyMethods(['calculateDiscountsRoundedByCurrency', '__construct'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())->method('calculateDiscountsRoundedByCurrency')->willReturn([null, null]);

        return $mock;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    private function createQuantifiedProducts(): array
    {
        $quantifiedProductMock = $this->createMock(QuantifiedProduct::class);

        return [$quantifiedProductMock, $quantifiedProductMock];
    }
}
