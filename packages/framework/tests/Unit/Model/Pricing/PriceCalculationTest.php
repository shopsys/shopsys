<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class PriceCalculationTest extends TestCase
{
    /**
     * @return array<int, array<'expectedPriceWithVat'|'priceWithoutVat'|'vatPercent', \Shopsys\FrameworkBundle\Component\Money\Money|string>>
     */
    public function applyVatPercentProvider(): array
    {
        return [
            [
                'priceWithoutVat' => Money::create(0),
                'vatPercent' => '21',
                'expectedPriceWithVat' => Money::create(0),
            ],
            [
                'priceWithoutVat' => Money::create(100),
                'vatPercent' => '0',
                'expectedPriceWithVat' => Money::create(100),
            ],
            [
                'priceWithoutVat' => Money::create(100),
                'vatPercent' => '21',
                'expectedPriceWithVat' => Money::create(121),
            ],
            [
                'priceWithoutVat' => Money::create('100.9'),
                'vatPercent' => '21.1',
                'expectedPriceWithVat' => Money::create('122.1899'),
            ],
        ];
    }

    /**
     * @dataProvider applyVatPercentProvider
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithoutVat
     * @param string $vatPercent
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $expectedPriceWithVat
     */
    public function testApplyVatPercent(
        Money $priceWithoutVat,
        string $vatPercent,
        Money $expectedPriceWithVat,
    ): void {
        $rounding = new Rounding();
        $priceCalculation = new PriceCalculation($rounding);
        $vatData = new VatData();
        $vatData->name = 'testVat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData, Domain::FIRST_DOMAIN_ID);

        $actualPriceWithVat = $priceCalculation->applyVatPercent($priceWithoutVat, $vat);

        $this->assertThat($actualPriceWithVat, new IsMoneyEqual($expectedPriceWithVat));
    }

    /**
     * @return array<'expectedVatAmount'|'priceWithVat'|'vatPercent', \Shopsys\FrameworkBundle\Component\Money\Money|'10'>[]|array<'expectedPriceWithVat'|'priceWithoutVat'|'vatPercent', \Shopsys\FrameworkBundle\Component\Money\Money|'21'|'0'>[]
     */
    public function getVatAmountByPriceWithVatProvider(): array
    {
        return [
            [
                'priceWithVat' => Money::create(0),
                'vatPercent' => '10',
                'expectedVatAmount' => Money::create(0),
            ],
            [
                'priceWithoutVat' => Money::create(100),
                'vatPercent' => '0',
                'expectedPriceWithVat' => Money::create(0),
            ],
            [
                'priceWithoutVat' => Money::create(100),
                'vatPercent' => '21',
                'expectedPriceWithVat' => Money::create('17.36'),
            ],
        ];
    }

    /**
     * @dataProvider getVatAmountByPriceWithVatProvider
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @param string $vatPercent
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $expectedVatAmount
     */
    public function testGetVatAmountByPriceWithVat(
        Money $priceWithVat,
        string $vatPercent,
        Money $expectedVatAmount,
    ): void {
        $rounding = new Rounding();
        $priceCalculation = new PriceCalculation($rounding);
        $vatData = new VatData();
        $vatData->name = 'testVat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData, Domain::FIRST_DOMAIN_ID);

        $actualVatAmount = $priceCalculation->getVatAmountByPriceWithVat($priceWithVat, $vat);

        $this->assertThat($actualVatAmount, new IsMoneyEqual($expectedVatAmount));
    }
}
