<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Transport\DeliveryDate;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Symfony\Component\Clock\DatePoint;

final class TransportExpectedDeliveryDateCalculationTest extends TestCase
{
    private const int DAYS_UNTIL_DELIVERY = 4;
    private const string NOW = '2026-07-16 12:00:00';
    private const string STANDARD_DELIVERY_DATE = '2026-07-20 00:00:00'; // today + DAYS_UNTIL_DELIVERY
    private const string SOONER_RESTOCKING_DATE = '2026-07-22 00:00:00';
    private const string RESTOCKING_DATE = '2026-07-26 00:00:00';
    private const string DELIVERY_DATE_AFTER_RESTOCKING = '2026-07-30 00:00:00'; // RESTOCKING_DATE + DAYS_UNTIL_DELIVERY

    /**
     * @return iterable<string, array{cartItems: array<array{stockQuantity: int|null, quantity: int, expectedRestockingDate: string|null}>, daysUntilDelivery: int, expectedDeliveryDate: string|null}>
     */
    public static function getExpectedDeliveryDateData(): iterable
    {
        yield 'standard delivery date for an empty cart' => [
            'cartItems' => [],
            'daysUntilDelivery' => self::DAYS_UNTIL_DELIVERY,
            'expectedDeliveryDate' => self::STANDARD_DELIVERY_DATE,
        ];

        yield 'standard delivery date when every item is covered by the stock' => [
            'cartItems' => [
                ['stockQuantity' => 10, 'quantity' => 5, 'expectedRestockingDate' => null],
                ['stockQuantity' => 3, 'quantity' => 3, 'expectedRestockingDate' => self::RESTOCKING_DATE],
            ],
            'daysUntilDelivery' => self::DAYS_UNTIL_DELIVERY,
            'expectedDeliveryDate' => self::STANDARD_DELIVERY_DATE,
        ];

        yield 'null when an awaited item has no valid restocking date' => [
            'cartItems' => [
                ['stockQuantity' => 0, 'quantity' => 2, 'expectedRestockingDate' => self::RESTOCKING_DATE],
                ['stockQuantity' => 0, 'quantity' => 1, 'expectedRestockingDate' => null],
            ],
            'daysUntilDelivery' => self::DAYS_UNTIL_DELIVERY,
            'expectedDeliveryDate' => null,
        ];

        yield 'worst restocking date plus the days until delivery' => [
            'cartItems' => [
                ['stockQuantity' => 0, 'quantity' => 2, 'expectedRestockingDate' => self::SOONER_RESTOCKING_DATE],
                ['stockQuantity' => 0, 'quantity' => 1, 'expectedRestockingDate' => self::RESTOCKING_DATE],
            ],
            'daysUntilDelivery' => self::DAYS_UNTIL_DELIVERY,
            'expectedDeliveryDate' => self::DELIVERY_DATE_AFTER_RESTOCKING,
        ];

        yield 'restocking date of a partially stocked item counts as well' => [
            'cartItems' => [
                ['stockQuantity' => 2, 'quantity' => 5, 'expectedRestockingDate' => self::RESTOCKING_DATE],
            ],
            'daysUntilDelivery' => 0,
            'expectedDeliveryDate' => self::RESTOCKING_DATE,
        ];

        yield 'item with an unknown stock quantity counts as awaiting restocking' => [
            'cartItems' => [
                ['stockQuantity' => null, 'quantity' => 1, 'expectedRestockingDate' => self::RESTOCKING_DATE],
            ],
            'daysUntilDelivery' => self::DAYS_UNTIL_DELIVERY,
            'expectedDeliveryDate' => self::DELIVERY_DATE_AFTER_RESTOCKING,
        ];
    }

    /**
     * @param array<array{stockQuantity: int|null, quantity: int, expectedRestockingDate: string|null}> $cartItems
     */
    #[DataProvider('getExpectedDeliveryDateData')]
    public function testCalculateExpectedDeliveryDateByCart(
        array $cartItems,
        int $daysUntilDelivery,
        ?string $expectedDeliveryDate,
    ): void {
        $stockQuantityMap = [];
        $restockingDateMap = [];
        $cartItemStubs = [];

        foreach ($cartItems as $cartItemData) {
            $productStub = $this->createStub(Product::class);
            $stockQuantityMap[] = [$productStub, Domain::FIRST_DOMAIN_ID, $cartItemData['stockQuantity']];
            $restockingDateMap[] = [
                $productStub,
                Domain::FIRST_DOMAIN_ID,
                $cartItemData['expectedRestockingDate'] === null
                    ? null
                    : new DatePoint($cartItemData['expectedRestockingDate'], new DateTimeZone('UTC')),
            ];

            $cartItemStub = $this->createStub(CartItem::class);
            $cartItemStub->method('getProduct')->willReturn($productStub);
            $cartItemStub->method('getQuantity')->willReturn($cartItemData['quantity']);
            $cartItemStubs[] = $cartItemStub;
        }

        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantityByProductAndDomainId')
            ->willReturnMap($stockQuantityMap);
        $productAvailabilityFacadeStub->method('findValidExpectedRestockingDate')
            ->willReturnMap($restockingDateMap);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn($cartItemStubs);

        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getDaysUntilDelivery')->willReturn($daysUntilDelivery);

        $clockStub = $this->createStub(ClockInterface::class);
        $clockStub->method('now')->willReturn(new DatePoint(self::NOW, new DateTimeZone('UTC')));

        $displayTimeZoneProviderStub = $this->createStub(DisplayTimeZoneProviderInterface::class);
        $displayTimeZoneProviderStub->method('getDisplayTimeZoneByDomainId')->willReturn(new DateTimeZone('UTC'));

        $transportExpectedDeliveryDateCalculation = new TransportExpectedDeliveryDateCalculation(
            $productAvailabilityFacadeStub,
            $clockStub,
            $displayTimeZoneProviderStub,
        );

        $deliveryDate = $transportExpectedDeliveryDateCalculation->calculateExpectedDeliveryDate(
            $transportStub,
            $cartStub,
            Domain::FIRST_DOMAIN_ID,
        );

        $this->assertDeliveryDateSame($expectedDeliveryDate, $deliveryDate);
    }

    public function testStandardDeliveryDateIsReturnedWithoutCart(): void
    {
        $clockStub = $this->createStub(ClockInterface::class);
        $clockStub->method('now')->willReturn(new DatePoint(self::NOW, new DateTimeZone('UTC')));

        $displayTimeZoneProviderStub = $this->createStub(DisplayTimeZoneProviderInterface::class);
        $displayTimeZoneProviderStub->method('getDisplayTimeZoneByDomainId')->willReturn(new DateTimeZone('UTC'));

        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getDaysUntilDelivery')->willReturn(self::DAYS_UNTIL_DELIVERY);

        $transportExpectedDeliveryDateCalculation = new TransportExpectedDeliveryDateCalculation(
            $this->createStub(ProductAvailabilityFacade::class),
            $clockStub,
            $displayTimeZoneProviderStub,
        );

        $deliveryDate = $transportExpectedDeliveryDateCalculation->calculateExpectedDeliveryDate(
            $transportStub,
            null,
            Domain::FIRST_DOMAIN_ID,
        );

        $this->assertDeliveryDateSame(self::STANDARD_DELIVERY_DATE, $deliveryDate);
    }

    public function testTodayIsDeterminedInTheDisplayTimeZone(): void
    {
        // 2026-07-16 23:30 UTC is already 2026-07-17 01:30 in Europe/Prague
        $clockStub = $this->createStub(ClockInterface::class);
        $clockStub->method('now')->willReturn(new DatePoint('2026-07-16 23:30:00', new DateTimeZone('UTC')));

        $displayTimeZoneProviderStub = $this->createStub(DisplayTimeZoneProviderInterface::class);
        $displayTimeZoneProviderStub->method('getDisplayTimeZoneByDomainId')->willReturn(new DateTimeZone('Europe/Prague'));

        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getDaysUntilDelivery')->willReturn(self::DAYS_UNTIL_DELIVERY);

        $transportExpectedDeliveryDateCalculation = new TransportExpectedDeliveryDateCalculation(
            $this->createStub(ProductAvailabilityFacade::class),
            $clockStub,
            $displayTimeZoneProviderStub,
        );

        $deliveryDate = $transportExpectedDeliveryDateCalculation->calculateExpectedDeliveryDate(
            $transportStub,
            null,
            Domain::FIRST_DOMAIN_ID,
        );

        $this->assertDeliveryDateSame('2026-07-21 00:00:00', $deliveryDate);
    }

    private function assertDeliveryDateSame(?string $expectedDeliveryDate, ?DateTimeImmutable $deliveryDate): void
    {
        $this->assertSame($expectedDeliveryDate, $deliveryDate?->format('Y-m-d H:i:s'));
    }
}
