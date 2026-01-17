<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Payment;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactory;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class PaymentPriceCalculationTest extends TestCase
{
    public static function calculateIndependentPriceProvider()
    {
        return [
            [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITHOUT_VAT,
                'inputPrice' => Money::create(6999),
                'vatPercent' => '21',
                'priceWithoutVat' => Money::create(6999),
                'priceWithVat' => Money::create(8469),
            ],
            [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITH_VAT,
                'inputPrice' => Money::create('6999.99'),
                'vatPercent' => '21',
                'priceWithoutVat' => Money::create('5785.12'),
                'priceWithVat' => Money::create(7000),
            ],
        ];
    }

    public static function calculatePriceProvider()
    {
        return [
            [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITHOUT_VAT,
                'inputPrice' => Money::create(6999),
                'vatPercent' => '21',
                'priceWithoutVat' => Money::create(6999),
                'priceWithVat' => Money::create(8469),
                'productsPrice' => new Price(Money::create(100), Money::create(121)),
                'forceFreePrice' => false,
            ],
            [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITH_VAT,
                'inputPrice' => Money::create('6999.99'),
                'vatPercent' => '21',
                'priceWithoutVat' => Money::create('5785.12'),
                'priceWithVat' => Money::create(7000),
                'productsPrice' => new Price(Money::create(1000), Money::create(1210)),
                'forceFreePrice' => false,
            ],
            [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITHOUT_VAT,
                'inputPrice' => Money::create(6999),
                'vatPercent' => '21',
                'priceWithoutVat' => Money::create(6999),
                'priceWithVat' => Money::create(8469),
                'productsPrice' => new Price(Money::create(100), Money::create(121)),
                'forceFreePrice' => true,
            ],
        ];
    }

    #[DataProvider('calculateIndependentPriceProvider')]
    public function testCalculateIndependentPrice(
        int $inputPriceType,
        Money $inputPrice,
        string $vatPercent,
        Money $priceWithoutVat,
        Money $priceWithVat,
    ): void {
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->onlyMethods(['getInputPriceType'])
            ->disableOriginalConstructor()
            ->getMock();
        $pricingSettingMock
            ->expects($this->any())->method('getInputPriceType')
                ->willReturn($inputPriceType);
        $freeTransportAndPaymentFacadeMock = $this->createMock(FreeTransportAndPaymentFacade::class);

        $rounding = new Rounding();

        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

        $paymentPriceCalculation = new PaymentPriceCalculation($basePriceCalculation, $pricingSettingMock, $freeTransportAndPaymentFacadeMock);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData, Domain::FIRST_DOMAIN_ID);
        $currencyData = new CurrencyData();
        $currencyData->name = 'currencyName';
        $currencyData->code = Currency::CODE_CZK;
        $currencyData->exchangeRate = '1.0';
        $currencyData->minFractionDigits = 2;
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $currency = new Currency($currencyData);

        $paymentData = new PaymentData();
        $paymentData->name = ['cs' => 'paymentName'];
        $paymentData->enabled = [
            Domain::FIRST_DOMAIN_ID => true,
        ];
        $paymentData->vatsIndexedByDomainId = [
            Domain::FIRST_DOMAIN_ID => $vat,
        ];
        $payment = new Payment($paymentData);
        $payment->setPrice($inputPrice, Domain::FIRST_DOMAIN_ID);
        $payment->addPrice(
            (new PaymentPriceFactory(new EntityNameResolver([])))->create(
                $payment,
                $inputPrice,
                Domain::FIRST_DOMAIN_ID,
            ),
        );

        $price = $paymentPriceCalculation->calculateIndependentPrice($payment, $currency, Domain::FIRST_DOMAIN_ID);

        $this->assertThat($price->getPriceWithoutVat(), new IsMoneyEqual($priceWithoutVat));
        $this->assertThat($price->getPriceWithVat(), new IsMoneyEqual($priceWithVat));
    }

    #[DataProvider('calculatePriceProvider')]
    public function testCalculatePrice(
        int $inputPriceType,
        Money $inputPrice,
        string $vatPercent,
        Money $priceWithoutVat,
        Money $priceWithVat,
        Price $productsPrice,
        bool $forceFreePrice,
    ): void {
        $priceLimit = Money::create(1000);
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->onlyMethods(['getInputPriceType', 'getFreeTransportAndPaymentPriceLimit'])
            ->disableOriginalConstructor()
            ->getMock();
        $pricingSettingMock
            ->expects($this->any())->method('getInputPriceType')
                ->willReturn($inputPriceType);
        $freeTransportAndPaymentFacadeMock = $this->getMockBuilder(FreeTransportAndPaymentFacade::class)
            ->onlyMethods(['isFree'])
            ->disableOriginalConstructor()
            ->getMock();
        $priceShouldBeFree = $forceFreePrice || $productsPrice->getPriceWithVat()->isGreaterThan($priceLimit);
        $freeTransportAndPaymentFacadeMock
            ->expects($this->any())->method('isFree')
            ->willReturn($priceShouldBeFree);

        $rounding = new Rounding();

        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

        $paymentPriceCalculation = new PaymentPriceCalculation($basePriceCalculation, $pricingSettingMock, $freeTransportAndPaymentFacadeMock);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData, Domain::FIRST_DOMAIN_ID);
        $currencyData = new CurrencyData();
        $currencyData->name = 'currencyName';
        $currencyData->code = Currency::CODE_CZK;
        $currencyData->exchangeRate = '1.0';
        $currencyData->minFractionDigits = 2;
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $currency = new Currency($currencyData);

        $paymentData = new PaymentData();
        $paymentData->name = ['cs' => 'paymentName'];
        $paymentData->enabled = [
            Domain::FIRST_DOMAIN_ID => true,
        ];
        $paymentData->vatsIndexedByDomainId = [
            Domain::FIRST_DOMAIN_ID => $vat,
        ];
        $payment = new Payment($paymentData);
        $payment->setPrice($inputPrice, Domain::FIRST_DOMAIN_ID);
        $payment->addPrice(
            (new PaymentPriceFactory(new EntityNameResolver([])))->create(
                $payment,
                $inputPrice,
                Domain::FIRST_DOMAIN_ID,
            ),
        );

        $price = $paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $productsPrice,
            Domain::FIRST_DOMAIN_ID,
            $forceFreePrice,
        );

        if ($priceShouldBeFree) {
            $this->assertThat($price->getPriceWithoutVat(), new IsMoneyEqual(Money::zero()));
            $this->assertThat($price->getPriceWithVat(), new IsMoneyEqual(Money::zero()));
        } else {
            $this->assertThat($price->getPriceWithoutVat(), new IsMoneyEqual($priceWithoutVat));
            $this->assertThat($price->getPriceWithVat(), new IsMoneyEqual($priceWithVat));
        }
    }
}
