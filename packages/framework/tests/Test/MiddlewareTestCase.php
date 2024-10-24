<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class MiddlewareTestCase extends TestCase
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack
     */
    protected function createOrderProcessingStack(): OrderProcessingStack
    {
        $middleware = $this->createMock(OrderProcessorMiddlewareInterface::class);
        $middleware
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (OrderProcessingData $orderProcessingData, OrderProcessingStack $orderProcessingStack) {
                return $orderProcessingStack->processNext($orderProcessingData);
            });

        return new OrderProcessingStack([$middleware]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    protected function createOrderProcessingData(): OrderProcessingData
    {
        $orderItemTypeEnum = new OrderItemTypeEnum();
        $paymentTransactionRefundDataFactory = $this->createMock(PaymentTransactionRefundDataFactory::class);
        $orderItemDataFactory = $this->createMock(OrderItemDataFactory::class);
        $orderInputFactory = $this->createMock(OrderInputFactory::class);
        $orderProcessor = $this->createMock(OrderProcessor::class);

        $orderDataFactory = new OrderDataFactory(
            $orderItemDataFactory,
            $paymentTransactionRefundDataFactory,
            $orderItemTypeEnum,
            $orderInputFactory,
            $orderProcessor,
        );
        $orderData = $orderDataFactory->create();

        $orderInput = new OrderInput($this->createDomainConfigMock());

        return new OrderProcessingData($orderInput, $orderData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory
     */
    protected function createOrderItemDataFactory(): OrderItemDataFactory
    {
        $orderItemPriceCalculation = $this->createMock(OrderItemPriceCalculation::class);

        return new OrderItemDataFactory($orderItemPriceCalculation);
    }

    /**
     * @param string $currencyCode
     * @param string $roundingType
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected function createCurrencyFacade(
        string $currencyCode = Currency::CODE_EUR,
        string $roundingType = Currency::ROUNDING_TYPE_HUNDREDTHS,
    ): CurrencyFacade {
        $currencyFacade = $this->createMock(CurrencyFacade::class);

        $currency = $this->createMock(Currency::class);
        $currency->method('getCode')->willReturn($currencyCode);
        $currency->method('getRoundingType')->willReturn($roundingType);

        $currencyFacade->method('getDomainDefaultCurrencyByDomainId')
            ->willReturn($currency);

        return $currencyFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    protected function createVat(): Vat
    {
        return $this->createMock(Vat::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    protected function createDomainConfigMock(): DomainConfig
    {
        $domainConfigMock = $this->createMock(DomainConfig::class);

        $domainConfigMock->method('getId')->willReturn(1);
        $domainConfigMock->method('getLocale')->willReturn('en');

        return $domainConfigMock;
    }
}
