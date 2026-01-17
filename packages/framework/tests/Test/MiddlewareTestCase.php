<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class MiddlewareTestCase extends TestCase
{
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

    protected function createOrderProcessingData(): OrderProcessingData
    {
        $orderItemTypeEnum = new OrderItemTypeEnum();
        $paymentTransactionRefundDataFactory = $this->createMock(PaymentTransactionRefundDataFactory::class);
        $orderItemDataFactory = $this->createMock(OrderItemDataFactory::class);
        $clockMock = $this->createMock(ClockInterface::class);

        $withdrawalRequestDataFactory = $this->createMock(WithdrawalRequestDataFactory::class);
        $withdrawalRequestFacade = $this->createMock(WithdrawalRequestFacade::class);

        $orderDataFactory = new OrderDataFactory(
            $orderItemDataFactory,
            $paymentTransactionRefundDataFactory,
            $orderItemTypeEnum,
            $withdrawalRequestDataFactory,
            $withdrawalRequestFacade,
            $clockMock,
        );
        $orderData = $orderDataFactory->create();

        $orderInput = new OrderInput($this->createDomainConfigMock());

        return new OrderProcessingData($orderInput, $orderData);
    }

    protected function createOrderItemDataFactory(): OrderItemDataFactory
    {
        $orderItemPriceCalculation = $this->createMock(OrderItemPriceCalculation::class);
        $pricingSettingMock = $this->createMock(PricingSetting::class);

        return new OrderItemDataFactory($orderItemPriceCalculation, $pricingSettingMock);
    }

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

    protected function createVat(): Vat
    {
        return $this->createMock(Vat::class);
    }

    protected function createDomainConfigMock(): DomainConfig
    {
        $domainConfigMock = $this->createMock(DomainConfig::class);

        $domainConfigMock->method('getId')->willReturn(1);
        $domainConfigMock->method('getLocale')->willReturn('en');

        return $domainConfigMock;
    }
}
