<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Pricing\Currency;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidRoundingTypeException;

class CurrencyTest extends TestCase
{
    /**
     * @return iterable<string, array{roundingType: string}>
     */
    public static function validRoundingTypeProvider(): iterable
    {
        yield 'hundredths' => [
            'roundingType' => Currency::ROUNDING_TYPE_HUNDREDTHS,
        ];

        yield 'fifties' => [
            'roundingType' => Currency::ROUNDING_TYPE_FIFTIES,
        ];

        yield 'integer' => [
            'roundingType' => Currency::ROUNDING_TYPE_INTEGER,
        ];
    }

    #[DataProvider('validRoundingTypeProvider')]
    public function testValidRoundingTypeIsStored(string $roundingType): void
    {
        $currency = new Currency($this->createCurrencyData($roundingType));

        $this->assertSame($roundingType, $currency->getRoundingType());
    }

    public function testInvalidRoundingTypeIsRejectedOnConstruct(): void
    {
        $this->expectException(InvalidRoundingTypeException::class);

        new Currency($this->createCurrencyData('thousandths'));
    }

    public function testInvalidRoundingTypeIsRejectedOnEdit(): void
    {
        $currency = new Currency($this->createCurrencyData(Currency::ROUNDING_TYPE_INTEGER));

        $this->expectException(InvalidRoundingTypeException::class);

        $currency->edit($this->createCurrencyData('thousandths'));
    }

    protected function createCurrencyData(string $roundingType): CurrencyData
    {
        $currencyData = new CurrencyData();
        $currencyData->name = 'currencyName';
        $currencyData->code = Currency::CODE_CZK;
        $currencyData->exchangeRate = '1.0';
        $currencyData->minFractionDigits = 2;
        $currencyData->roundingType = $roundingType;

        return $currencyData;
    }
}
