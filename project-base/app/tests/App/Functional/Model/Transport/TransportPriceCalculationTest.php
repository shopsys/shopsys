<?php

declare(strict_types=1);

namespace App\Functional\Model\Transport;

use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\SettingValueDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Transport\Transport;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Tests\App\Test\TransactionFunctionalTestCase;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class TransportPriceCalculationTest extends TransactionFunctionalTestCase
{
    private const int CART_TOTAL_WEIGHT_ABOVE_ALL_LIMITS = 10001;

    /**
     * @inject
     */
    private TransportPriceCalculation $transportPriceCalculation;

    /**
     * @inject
     */
    private PriceConverter $priceConverter;

    public function testCalculatePriceThrowsExceptionWhenWeightLimitIsExceeded(): void
    {
        $transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class);
        $this->expectException(TransportPriceNotFoundException::class);
        $this->transportPriceCalculation->calculatePrice($transportCzechPost, Price::zero(), Domain::FIRST_DOMAIN_ID, self::CART_TOTAL_WEIGHT_ABOVE_ALL_LIMITS, false);
    }

    /**
     * @param int $cartTotalWeight
     * @param int $expectedMoneyAmountWithoutVat
     */
    #[DataProvider('calculatePriceDataProvider')]
    public function testCalculatePrice(int $cartTotalWeight, int $expectedMoneyAmountWithoutVat): void
    {
        $transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class);

        $calculatedPrice = $this->transportPriceCalculation->calculatePrice($transportCzechPost, Price::zero(), Domain::FIRST_DOMAIN_ID, $cartTotalWeight, false);

        $expectedTransportPriceWithoutVat = $this->priceConverter->convertPriceWithoutVatToDomainDefaultCurrencyPrice(
            Money::create($expectedMoneyAmountWithoutVat),
            $this->getReference(CurrencyDataFixture::CURRENCY_CZK),
            Domain::FIRST_DOMAIN_ID,
        );

        $this->assertThat($calculatedPrice->getPriceWithoutVat(), new IsMoneyEqual($expectedTransportPriceWithoutVat));
    }

    /**
     * @return array
     */
    public static function calculatePriceDataProvider(): array
    {
        return [
            'cart total weight is in the 1st price level' => [
                'cartTotalWeight' => 0,
                'expectedMoneyAmountWithoutVat' => 100,
            ],
            'cart total weight is in the 2nd price level' => [
                'cartTotalWeight' => 5001,
                'expectedMoneyAmountWithoutVat' => 200,
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $productsPrice
     * @param bool $forceFreeTransport
     */
    #[DataProvider('calculateFreePriceDataProvider')]
    public function testCalculateFreePrice(Price $productsPrice, bool $forceFreeTransport): void
    {
        $transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class);

        $calculatedPrice = $this->transportPriceCalculation->calculatePrice($transportCzechPost, $productsPrice, Domain::FIRST_DOMAIN_ID, 0, $forceFreeTransport);

        $this->assertTrue($calculatedPrice->getPriceWithoutVat()->isZero());
    }

    /**
     * @return iterable
     */
    public static function calculateFreePriceDataProvider(): iterable
    {
        yield 'products price reached the free price limit' => [
            'productsPrice' => new Price(Money::create(SettingValueDataFixture::FREE_TRANSPORT_AND_PAYMENT_LIMIT), Money::create(SettingValueDataFixture::FREE_TRANSPORT_AND_PAYMENT_LIMIT)),
            'forceFreeTransport' => false,
        ];

        yield 'free transport price is forced' => [
            'productsPrice' => new Price(Money::create(1), Money::create(1)),
            'forceFreeTransport' => true,
        ];
    }
}
