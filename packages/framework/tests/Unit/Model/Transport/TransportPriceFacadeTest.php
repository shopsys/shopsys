<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Transport;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPrice;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceFacade;

class TransportPriceFacadeTest extends TestCase
{
    private const int SECOND_DOMAIN_ID = 2;

    /**
     * @return iterable<string, array{cartTotalWeight: int, expectedPriceAmount: string}>
     */
    public static function closestWeightPriceProvider(): iterable
    {
        yield 'lightest cart uses the lowest weight level' => [
            'cartTotalWeight' => 0,
            'expectedPriceAmount' => '100',
        ];

        yield 'weight on the level limit still uses that level' => [
            'cartTotalWeight' => 5000,
            'expectedPriceAmount' => '100',
        ];

        yield 'weight above the first level uses the next level' => [
            'cartTotalWeight' => 5001,
            'expectedPriceAmount' => '200',
        ];

        yield 'weight above all limits falls back to the unlimited level' => [
            'cartTotalWeight' => 50000,
            'expectedPriceAmount' => '300',
        ];
    }

    #[DataProvider('closestWeightPriceProvider')]
    public function testClosestWeightPriceIsSelectedFromTransportPrices(
        int $cartTotalWeight,
        string $expectedPriceAmount,
    ): void {
        $transport = $this->createTransportWithPrices([
            [Domain::FIRST_DOMAIN_ID, null, '300'],
            [Domain::FIRST_DOMAIN_ID, 5000, '100'],
            [self::SECOND_DOMAIN_ID, null, '999'],
            [Domain::FIRST_DOMAIN_ID, 10000, '200'],
        ]);

        $transportPrice = new TransportPriceFacade()->getTransportPriceOnDomainByTransportAndClosestWeight(
            Domain::FIRST_DOMAIN_ID,
            $transport,
            $cartTotalWeight,
        );

        $this->assertSame($expectedPriceAmount, $transportPrice->getPrice()->getAmount());
    }

    public function testUnlimitedPriceIsUsedOnlyWhenNoWeightLevelIsSufficient(): void
    {
        $transport = $this->createTransportWithPrices([
            [Domain::FIRST_DOMAIN_ID, null, '300'],
            [Domain::FIRST_DOMAIN_ID, 5000, '100'],
        ]);

        $transportPriceFacade = new TransportPriceFacade();

        $this->assertSame('100', $transportPriceFacade->getTransportPriceOnDomainByTransportAndClosestWeight(Domain::FIRST_DOMAIN_ID, $transport, 4000)->getPrice()->getAmount());
        $this->assertSame('300', $transportPriceFacade->getTransportPriceOnDomainByTransportAndClosestWeight(Domain::FIRST_DOMAIN_ID, $transport, 6000)->getPrice()->getAmount());
    }

    public function testExceptionIsThrownWhenNoPriceCoversTheCartWeightOnDomain(): void
    {
        $transport = $this->createTransportWithPrices([
            [Domain::FIRST_DOMAIN_ID, 5000, '100'],
            [self::SECOND_DOMAIN_ID, null, '999'],
        ]);

        $this->expectException(TransportPriceNotFoundException::class);

        new TransportPriceFacade()->getTransportPriceOnDomainByTransportAndClosestWeight(Domain::FIRST_DOMAIN_ID, $transport, 5001);
    }

    /**
     * @param array<int, array{0: int, 1: int|null, 2: string}> $domainIdsMaxWeightsAndPriceAmounts
     */
    private function createTransportWithPrices(array $domainIdsMaxWeightsAndPriceAmounts): Transport
    {
        $transport = $this->createStub(Transport::class);
        $transportPrices = [];

        foreach ($domainIdsMaxWeightsAndPriceAmounts as [$domainId, $maxWeight, $priceAmount]) {
            $transportPrices[] = new TransportPrice($transport, Money::create($priceAmount), $domainId, $maxWeight);
        }

        $transport->method('getPrices')->willReturn($transportPrices);

        return $transport;
    }
}
