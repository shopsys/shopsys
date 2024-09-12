<?php

declare(strict_types=1);

namespace App\Functional\Model\Transport;

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
        $this->transportPriceCalculation->calculatePrice($transportCzechPost, Price::zero(), Domain::FIRST_DOMAIN_ID, self::CART_TOTAL_WEIGHT_ABOVE_ALL_LIMITS);
    }

    /**
     * @param int $cartTotalWeight
     * @param int $expectedMoneyAmountWithoutVat
     */
    #[DataProvider('calculatePriceDataProvider')]
    public function testCalculatePrice(int $cartTotalWeight, int $expectedMoneyAmountWithoutVat): void
    {
        $transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST, Transport::class);

        $calculatedPrice = $this->transportPriceCalculation->calculatePrice($transportCzechPost, Price::zero(), Domain::FIRST_DOMAIN_ID, $cartTotalWeight);

        $expectedTransportPriceWithoutVat = $this->priceConverter->convertPriceWithoutVatToDomainDefaultCurrencyPrice(
            Money::create($expectedMoneyAmountWithoutVat),
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID),
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
                'expectedMoneyAmountWithoutVat' => 4,
            ],
            'cart total weight is in the 2nd price level' => [
                'cartTotalWeight' => 5001,
                'expectedMoneyAmountWithoutVat' => 8,
            ],
        ];
    }
}
