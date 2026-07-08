<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\CurrencyMismatchException;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class PriceTest extends TestCase
{
    public function testAdd(): void
    {
        $price = new Price(Money::create(2), Money::create(3));
        $priceToAdd = new Price(Money::create(10), Money::create(15));
        $actualAddingResult = $price->add($priceToAdd);

        $this->assertThat($actualAddingResult->getPriceWithoutVat(), new IsMoneyEqual(Money::create(12)));
        $this->assertThat($actualAddingResult->getPriceWithVat(), new IsMoneyEqual(Money::create(18)));
        $this->assertThat($actualAddingResult->getVatAmount(), new IsMoneyEqual(Money::create(6)));
    }

    public function testAddIsImmutable(): void
    {
        $price = new Price(Money::create(2), Money::create(3));
        $priceToAdd = new Price(Money::create(10), Money::create(15));
        $price->add($priceToAdd);

        $this->assertThat($price->getPriceWithoutVat(), new IsMoneyEqual(Money::create(2)));
        $this->assertThat($price->getPriceWithVat(), new IsMoneyEqual(Money::create(3)));
        $this->assertThat($price->getVatAmount(), new IsMoneyEqual(Money::create(1)));
    }

    public function testSubtract(): void
    {
        $price = new Price(Money::create(2), Money::create(3));
        $priceToSubtract = new Price(Money::create(10), Money::create(15));
        $actualAddingResult = $price->subtract($priceToSubtract);

        $this->assertThat($actualAddingResult->getPriceWithoutVat(), new IsMoneyEqual(Money::create(-8)));
        $this->assertThat($actualAddingResult->getPriceWithVat(), new IsMoneyEqual(Money::create(-12)));
        $this->assertThat($actualAddingResult->getVatAmount(), new IsMoneyEqual(Money::create(-4)));
    }

    public function testSubtractIsImmutable(): void
    {
        $price = new Price(Money::create(2), Money::create(3));
        $priceToSubtract = new Price(Money::create(10), Money::create(15));
        $price->subtract($priceToSubtract);

        $this->assertThat($price->getPriceWithoutVat(), new IsMoneyEqual(Money::create(2)));
        $this->assertThat($price->getPriceWithVat(), new IsMoneyEqual(Money::create(3)));
        $this->assertThat($price->getVatAmount(), new IsMoneyEqual(Money::create(1)));
    }

    public function testInverse(): void
    {
        $price = new Price(Money::create(2), Money::create(3));
        $actualInverseResult = $price->inverse();

        $this->assertThat($actualInverseResult->getPriceWithoutVat(), new IsMoneyEqual(Money::create(-2)));
        $this->assertThat($actualInverseResult->getPriceWithVat(), new IsMoneyEqual(Money::create(-3)));
        $this->assertThat($actualInverseResult->getVatAmount(), new IsMoneyEqual(Money::create(-1)));
    }

    public function testInverseIsImmutable(): void
    {
        $price = new Price(Money::create(2), Money::create(3));
        $price->inverse();

        $this->assertThat($price->getPriceWithoutVat(), new IsMoneyEqual(Money::create(2)));
        $this->assertThat($price->getPriceWithVat(), new IsMoneyEqual(Money::create(3)));
        $this->assertThat($price->getVatAmount(), new IsMoneyEqual(Money::create(1)));
    }

    public function testAddWithSameCurrencyKeepsCurrency(): void
    {
        $currency = $this->createCurrency(Currency::CODE_CZK);
        $price = new Price(Money::create(2), Money::create(3), $currency);
        $priceToAdd = new Price(Money::create(10), Money::create(15), $currency);

        $this->assertSame($currency, $price->add($priceToAdd)->getCurrency());
    }

    public function testAddWithDifferentCurrenciesThrowsException(): void
    {
        $price = new Price(Money::create(2), Money::create(3), $this->createCurrency(Currency::CODE_CZK));
        $priceToAdd = new Price(Money::create(10), Money::create(15), $this->createCurrency(Currency::CODE_EUR));

        $this->expectException(CurrencyMismatchException::class);
        $price->add($priceToAdd);
    }

    public function testSubtractWithDifferentCurrenciesThrowsException(): void
    {
        $price = new Price(Money::create(2), Money::create(3), $this->createCurrency(Currency::CODE_CZK));
        $priceToSubtract = new Price(Money::create(10), Money::create(15), $this->createCurrency(Currency::CODE_EUR));

        $this->expectException(CurrencyMismatchException::class);
        $price->subtract($priceToSubtract);
    }

    public function testCurrencyLessPriceAdoptsCurrencyOfOtherOperand(): void
    {
        $currency = $this->createCurrency(Currency::CODE_CZK);
        $currencyLessPrice = Price::zero();
        $priceWithCurrency = new Price(Money::create(10), Money::create(15), $currency);

        $this->assertSame($currency, $currencyLessPrice->add($priceWithCurrency)->getCurrency());
        $this->assertSame($currency, $priceWithCurrency->add($currencyLessPrice)->getCurrency());
        $this->assertSame($currency, $priceWithCurrency->subtract($currencyLessPrice)->getCurrency());
    }

    public function testMultiplyAndInversePropagateCurrency(): void
    {
        $currency = $this->createCurrency(Currency::CODE_CZK);
        $price = new Price(Money::create(2), Money::create(3), $currency);

        $this->assertSame($currency, $price->multiply(2)->getCurrency());
        $this->assertSame($currency, $price->inverse()->getCurrency());
    }

    public function testZeroIsCurrencyNeutral(): void
    {
        $this->assertNull(Price::zero()->getCurrency());
    }

    private function createCurrency(string $code): Currency
    {
        $currencyData = new CurrencyData();
        $currencyData->name = $code;
        $currencyData->code = $code;
        $currencyData->exchangeRate = Currency::DEFAULT_EXCHANGE_RATE;
        $currencyData->minFractionDigits = Currency::DEFAULT_MIN_FRACTION_DIGITS;
        $currencyData->roundingType = Currency::DEFAULT_ROUNDING_TYPE;
        $currencyData->roundingPlacesPriceWithoutVat = Currency::DEFAULT_ROUNDING_PLACES_PRICE_WITHOUT_VAT;

        return new Currency($currencyData);
    }
}
