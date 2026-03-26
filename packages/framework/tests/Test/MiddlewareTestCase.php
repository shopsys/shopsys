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
        $paymentTransactionRefundDataFactory = $this->createStub(PaymentTransactionRefundDataFactory::class);
        $orderItemDataFactory = $this->createStub(OrderItemDataFactory::class);
        $clockStub = $this->createStub(ClockInterface::class);

        $withdrawalRequestDataFactory = $this->createStub(WithdrawalRequestDataFactory::class);
        $withdrawalRequestFacade = $this->createStub(WithdrawalRequestFacade::class);

        $orderDataFactory = new OrderDataFactory(
            $orderItemDataFactory,
            $paymentTransactionRefundDataFactory,
            $orderItemTypeEnum,
            $withdrawalRequestDataFactory,
            $withdrawalRequestFacade,
            $clockStub,
        );
        $orderData = $orderDataFactory->create();

        $orderInput = new OrderInput($this->createDomainConfigStub());

        return new OrderProcessingData($orderInput, $orderData);
    }

    protected function createOrderItemDataFactory(): OrderItemDataFactory
    {
        $orderItemPriceCalculation = $this->createStub(OrderItemPriceCalculation::class);
        $pricingSettingStub = $this->createStub(PricingSetting::class);

        return new OrderItemDataFactory($orderItemPriceCalculation, $pricingSettingStub);
    }

    protected function createCurrencyFacade(
        string $currencyCode = Currency::CODE_EUR,
        string $roundingType = Currency::ROUNDING_TYPE_HUNDREDTHS,
        int $roundingPlaces = Currency::DEFAULT_ROUNDING_PLACES_PRICE_WITHOUT_VAT,
        int $minFractionDigits = Currency::DEFAULT_MIN_FRACTION_DIGITS,
    ): CurrencyFacade {
        $currencyFacade = $this->createStub(CurrencyFacade::class);

        $currency = $this->createStub(Currency::class);
        $currency->method('getCode')->willReturn($currencyCode);
        $currency->method('getRoundingType')->willReturn($roundingType);
        $currency->method('getRoundingPlacesPriceWithoutVat')->willReturn($roundingPlaces);
        $currency->method('getMinFractionDigits')->willReturn($minFractionDigits);

        $currencyFacade->method('getDomainDefaultCurrencyByDomainId')
            ->willReturn($currency);

        return $currencyFacade;
    }

    protected function createVat(): Vat
    {
        return $this->createStub(Vat::class);
    }

    protected function createDomainConfigStub(): DomainConfig
    {
        $domainConfigStub = $this->createStub(DomainConfig::class);

        $domainConfigStub->method('getId')->willReturn(1);
        $domainConfigStub->method('getLocale')->willReturn('en');

        return $domainConfigStub;
    }
}
