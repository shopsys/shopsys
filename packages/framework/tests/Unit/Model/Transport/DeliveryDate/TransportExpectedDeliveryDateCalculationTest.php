<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Transport\DeliveryDate;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRange;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\StoreOpeningHoursProvider;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\Exception\TransportIsNotPersonalPickupException;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Symfony\Component\Clock\DatePoint;

final class TransportExpectedDeliveryDateCalculationTest extends TestCase
{
    private const int DAYS_UNTIL_DELIVERY = 4;
    private const string NOW = '2026-07-16 12:00:00'; // Thursday
    private const string STANDARD_DELIVERY_DATE = '2026-07-20 00:00:00'; // today + DAYS_UNTIL_DELIVERY
    private const string SOONER_RESTOCKING_DATE = '2026-07-22 00:00:00';
    private const string RESTOCKING_DATE = '2026-07-26 00:00:00'; // Sunday
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
    public function testExpectedDeliveryDateIsDerivedFromCart(
        array $cartItems,
        int $daysUntilDelivery,
        ?string $expectedDeliveryDate,
    ): void {
        $stockQuantitiesIndexedByProductId = [];
        $restockingDateMap = [];
        $quantifiedProducts = [];

        foreach ($cartItems as $index => $cartItemData) {
            $productId = $index + 1;
            $productStub = $this->createStub(Product::class);
            $productStub->method('getId')->willReturn($productId);

            $stockQuantitiesIndexedByProductId[$productId] = $cartItemData['stockQuantity'] ?? 0;

            $restockingDateMap[] = [
                $productStub,
                Domain::FIRST_DOMAIN_ID,
                $cartItemData['expectedRestockingDate'] === null
                    ? null
                    : new DatePoint($cartItemData['expectedRestockingDate'], new DateTimeZone('UTC')),
            ];

            $quantifiedProducts[] = new QuantifiedProduct($productStub, $cartItemData['quantity']);
        }

        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId')
            ->willReturn($stockQuantitiesIndexedByProductId);
        $productAvailabilityFacadeStub->method('findValidExpectedRestockingDate')
            ->willReturnMap($restockingDateMap);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getQuantifiedProducts')->willReturn($quantifiedProducts);

        $transportStub = $this->createTransportStubDeliveringAnyDay($daysUntilDelivery);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(productAvailabilityFacade: $productAvailabilityFacadeStub)
            ->calculateExpectedDeliveryDate($transportStub, $cartStub, Domain::FIRST_DOMAIN_ID);

        $this->assertDeliveryDateSame($expectedDeliveryDate, $deliveryDate);
    }

    public function testStandardDeliveryDateIsReturnedWithoutCart(): void
    {
        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation()->calculateExpectedDeliveryDate(
            $this->createTransportStubDeliveringAnyDay(self::DAYS_UNTIL_DELIVERY),
            null,
            Domain::FIRST_DOMAIN_ID,
        );

        $this->assertDeliveryDateSame(self::STANDARD_DELIVERY_DATE, $deliveryDate);
    }

    public function testTodayIsDeterminedInTheDisplayTimeZone(): void
    {
        // 2026-07-16 23:30 UTC is already 2026-07-17 01:30 in Europe/Prague
        $transportExpectedDeliveryDateCalculation = $this->createTransportExpectedDeliveryDateCalculation(
            now: '2026-07-16 23:30:00',
            displayTimezone: 'Europe/Prague',
        );

        $deliveryDate = $transportExpectedDeliveryDateCalculation->calculateExpectedDeliveryDate(
            $this->createTransportStubDeliveringAnyDay(self::DAYS_UNTIL_DELIVERY),
            null,
            Domain::FIRST_DOMAIN_ID,
        );

        // the delivery day 2026-07-21 in Europe/Prague, returned as a UTC instant
        $this->assertDeliveryDateSame('2026-07-20 22:00:00', $deliveryDate);
    }

    public function testDeliveryDateDerivedFromRestockingIsPostponed(): void
    {
        // the awaited item is expected to be restocked on Sunday, the transport delivers on working days only
        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                productAvailabilityFacade: $this->createSoldOutProductAvailabilityFacadeStub(self::RESTOCKING_DATE),
            )
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringOnNoSpecialDay(0),
                $this->createCartStubWithSingleProduct(),
                Domain::FIRST_DOMAIN_ID,
            );

        $this->assertDeliveryDateSame('2026-07-27 00:00:00', $deliveryDate);
    }

    public function testRestockingDateIsDeterminedInTheDisplayTimeZone(): void
    {
        // 2026-07-16 22:00 UTC is already Friday 2026-07-17 in Europe/Prague, which is a closed day of the e-shop
        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStub([], ['2026-07-17']),
                productAvailabilityFacade: $this->createSoldOutProductAvailabilityFacadeStub('2026-07-16 22:00:00'),
                displayTimezone: 'Europe/Prague',
            )
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringOnNoSpecialDay(0),
                $this->createCartStubWithSingleProduct(),
                Domain::FIRST_DOMAIN_ID,
            );

        // the closed Friday chains through the weekend to Monday 2026-07-20 in Europe/Prague, returned as a UTC instant
        $this->assertDeliveryDateSame('2026-07-19 22:00:00', $deliveryDate);
    }

    /**
     * @return iterable<string, array{daysUntilDelivery: int, publicHolidays: string[], internalClosedDays: string[], expectedDeliveryDate: string}>
     */
    public static function getPostponedDeliveryDateData(): iterable
    {
        // with NOW being Thursday, one day until delivery lands on Friday 2026-07-17, two days on Saturday 2026-07-18

        yield 'weekend delivery is postponed to Monday' => [
            'daysUntilDelivery' => 2,
            'publicHolidays' => [],
            'internalClosedDays' => [],
            'expectedDeliveryDate' => '2026-07-20 00:00:00',
        ];

        yield 'public holiday delivery chains through the weekend to Monday' => [
            'daysUntilDelivery' => 1,
            'publicHolidays' => ['2026-07-17'],
            'internalClosedDays' => [],
            'expectedDeliveryDate' => '2026-07-20 00:00:00',
        ];

        yield 'internal day delivery chains through the weekend to Monday' => [
            'daysUntilDelivery' => 1,
            'publicHolidays' => [],
            'internalClosedDays' => ['2026-07-17'],
            'expectedDeliveryDate' => '2026-07-20 00:00:00',
        ];

        yield 'Monday public holiday postpones the weekend delivery up to Tuesday' => [
            'daysUntilDelivery' => 2,
            'publicHolidays' => ['2026-07-20'],
            'internalClosedDays' => [],
            'expectedDeliveryDate' => '2026-07-21 00:00:00',
        ];
    }

    /**
     * @param string[] $publicHolidays
     * @param string[] $internalClosedDays
     */
    #[DataProvider('getPostponedDeliveryDateData')]
    public function testDeliveryDateIsPostponedToTheFirstAllowedDay(
        int $daysUntilDelivery,
        array $publicHolidays,
        array $internalClosedDays,
        string $expectedDeliveryDate,
    ): void {
        $transportStub = $this->createTransportStubDeliveringOnNoSpecialDay($daysUntilDelivery);

        $closedDayFacadeStub = $this->createClosedDayFacadeStub($publicHolidays, $internalClosedDays);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation($closedDayFacadeStub)
            ->calculateExpectedDeliveryDate($transportStub, null, Domain::FIRST_DOMAIN_ID);

        $this->assertDeliveryDateSame($expectedDeliveryDate, $deliveryDate);
    }

    /**
     * @return iterable<string, array{daysUntilDelivery: int, deliveryDaysOfWeek: int[], deliversOnPublicHolidays: bool, deliversOnInternalClosedDays: bool, publicHolidays: string[], internalClosedDays: string[], expectedDeliveryDate: string}>
     */
    public static function getKeptDeliveryDateData(): iterable
    {
        yield 'Saturday delivery is kept when the transport delivers every day of the week' => [
            'daysUntilDelivery' => 2, // Saturday
            'deliveryDaysOfWeek' => DateTimeHelper::ALL_DAYS_OF_WEEK,
            'deliversOnPublicHolidays' => false,
            'deliversOnInternalClosedDays' => false,
            'publicHolidays' => [],
            'internalClosedDays' => [],
            'expectedDeliveryDate' => '2026-07-18 00:00:00',
        ];

        yield 'public holiday delivery is kept when the transport delivers on public holidays' => [
            'daysUntilDelivery' => 1, // Friday
            'deliveryDaysOfWeek' => DateTimeHelper::WORKING_DAYS_OF_WEEK,
            'deliversOnPublicHolidays' => true,
            'deliversOnInternalClosedDays' => false,
            'publicHolidays' => ['2026-07-17'],
            'internalClosedDays' => [],
            'expectedDeliveryDate' => '2026-07-17 00:00:00',
        ];

        yield 'internal day delivery is kept when the transport delivers on internal days' => [
            'daysUntilDelivery' => 1, // Friday
            'deliveryDaysOfWeek' => DateTimeHelper::WORKING_DAYS_OF_WEEK,
            'deliversOnPublicHolidays' => false,
            'deliversOnInternalClosedDays' => true,
            'publicHolidays' => [],
            'internalClosedDays' => ['2026-07-17'],
            'expectedDeliveryDate' => '2026-07-17 00:00:00',
        ];
    }

    /**
     * @param int[] $deliveryDaysOfWeek
     * @param string[] $publicHolidays
     * @param string[] $internalClosedDays
     */
    #[DataProvider('getKeptDeliveryDateData')]
    public function testDeliveryDateIsKeptWhenTheTransportDeliversOnTheSpecialDay(
        int $daysUntilDelivery,
        array $deliveryDaysOfWeek,
        bool $deliversOnPublicHolidays,
        bool $deliversOnInternalClosedDays,
        array $publicHolidays,
        array $internalClosedDays,
        string $expectedDeliveryDate,
    ): void {
        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getDaysUntilDelivery')->willReturn($daysUntilDelivery);
        $this->stubDeliveryDaysOfWeek($transportStub, $deliveryDaysOfWeek);
        $transportStub->method('deliversOnPublicHolidays')->willReturn($deliversOnPublicHolidays);
        $transportStub->method('deliversOnInternalClosedDays')->willReturn($deliversOnInternalClosedDays);

        $closedDayFacadeStub = $this->createClosedDayFacadeStub($publicHolidays, $internalClosedDays);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation($closedDayFacadeStub)
            ->calculateExpectedDeliveryDate($transportStub, null, Domain::FIRST_DOMAIN_ID);

        $this->assertDeliveryDateSame($expectedDeliveryDate, $deliveryDate);
    }

    public function testDeliveryDateIsNullWhenNoDayWithinThePostponeBoundIsAllowed(): void
    {
        // a pathological configuration closes every single day of the postpone window
        $closedDays = [];
        $day = (new DatePoint(self::NOW, new DateTimeZone('UTC')))->modify('midnight');

        for ($i = 0; $i <= 366; $i++) {
            $closedDays[] = $this->createClosedDayStub(false, [], $day->format('Y-m-d'));
            $day = $day->modify('+1 day');
        }

        $closedDayFacadeStub = $this->createStub(ClosedDayFacade::class);
        $closedDayFacadeStub->method('getClosedDaysWithEagerLoadedExcludedStores')->willReturn($closedDays);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation($closedDayFacadeStub)
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringOnNoSpecialDay(0),
                null,
                Domain::FIRST_DOMAIN_ID,
            );

        $this->assertNull($deliveryDate);
    }

    public function testDeliveryDateIsPostponedToTheNextDayTheTransportDeliversOn(): void
    {
        // with NOW being Thursday, a transport delivering only on Tuesdays delivers on Tuesday next week
        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getDaysUntilDelivery')->willReturn(0);
        $transportStub->method('deliversOnPublicHolidays')->willReturn(true);
        $transportStub->method('deliversOnInternalClosedDays')->willReturn(true);
        $this->stubDeliveryDaysOfWeek($transportStub, [2]);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation()
            ->calculateExpectedDeliveryDate($transportStub, null, Domain::FIRST_DOMAIN_ID);

        $this->assertDeliveryDateSame('2026-07-21 00:00:00', $deliveryDate);
    }

    public function testDateBeingBothPublicHolidayAndInternalDayIsStillBlockedByTheInternalDay(): void
    {
        // Friday 2026-07-17 is a public holiday and an internal day at once; the transport delivers
        // on public holidays, but the internal day still blocks it and chains through the weekend
        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getDaysUntilDelivery')->willReturn(1);
        $this->stubDeliveryDaysOfWeek($transportStub, DateTimeHelper::WORKING_DAYS_OF_WEEK);
        $transportStub->method('deliversOnPublicHolidays')->willReturn(true);
        $transportStub->method('deliversOnInternalClosedDays')->willReturn(false);

        $closedDayFacadeStub = $this->createClosedDayFacadeStub(['2026-07-17'], ['2026-07-17']);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation($closedDayFacadeStub)
            ->calculateExpectedDeliveryDate($transportStub, null, Domain::FIRST_DOMAIN_ID);

        $this->assertDeliveryDateSame('2026-07-20 00:00:00', $deliveryDate);
    }

    /**
     * @return iterable<string, array{isPublicHoliday: bool}>
     */
    public static function getClosedDayTypeData(): iterable
    {
        yield 'public holiday' => ['isPublicHoliday' => true];

        yield 'internal day' => ['isPublicHoliday' => false];
    }

    #[DataProvider('getClosedDayTypeData')]
    public function testPersonalPickupIsNotPostponedWhenSomeStoreIsExcludedFromTheClosedDay(
        bool $isPublicHoliday,
    ): void {
        $openStoreStub = $this->createStoreStubOpenEveryDay();
        $closedStoreStub = $this->createStoreStubOpenEveryDay();

        $deliveryDate = $this->calculatePersonalPickupDeliveryDateOnClosedFriday(
            $this->createClosedDayStub($isPublicHoliday, [$openStoreStub]),
            [$closedStoreStub, $openStoreStub],
        );

        $this->assertDeliveryDateSame('2026-07-17 00:00:00', $deliveryDate);
    }

    #[DataProvider('getClosedDayTypeData')]
    public function testPersonalPickupIsPostponedWhenTheClosedDayAppliesToEveryStore(
        bool $isPublicHoliday,
    ): void {
        $storeStub = $this->createStoreStubOpenEveryDay();

        $deliveryDate = $this->calculatePersonalPickupDeliveryDateOnClosedFriday(
            $this->createClosedDayStub($isPublicHoliday),
            [$storeStub],
        );

        // Friday is blocked by the closed day, the stores are open on Saturday
        $this->assertDeliveryDateSame('2026-07-18 00:00:00', $deliveryDate);
    }

    #[DataProvider('getClosedDayTypeData')]
    public function testSelectedStoreDeliveryDateIsPostponedEvenWhenAnotherStoreIsOpen(
        bool $isPublicHoliday,
    ): void {
        $openStoreStub = $this->createStoreStubOpenEveryDay();
        $selectedStoreStub = $this->createStoreStubOpenEveryDay();

        $deliveryDate = $this->calculatePersonalPickupDeliveryDateOnClosedFriday(
            $this->createClosedDayStub($isPublicHoliday, [$openStoreStub]),
            [$selectedStoreStub, $openStoreStub],
            $selectedStoreStub,
        );

        // the closed Friday moves the pickup at the selected store to Saturday
        $this->assertDeliveryDateSame('2026-07-18 00:00:00', $deliveryDate);
    }

    #[DataProvider('getClosedDayTypeData')]
    public function testSelectedStoreDeliveryDateIsKeptWhenTheStoreIsExcludedFromTheClosedDay(
        bool $isPublicHoliday,
    ): void {
        $selectedStoreStub = $this->createStoreStubOpenEveryDay();
        $closedStoreStub = $this->createStoreStubOpenEveryDay();

        $deliveryDate = $this->calculatePersonalPickupDeliveryDateOnClosedFriday(
            $this->createClosedDayStub($isPublicHoliday, [$selectedStoreStub]),
            [$selectedStoreStub, $closedStoreStub],
            $selectedStoreStub,
        );

        $this->assertDeliveryDateSame('2026-07-17 00:00:00', $deliveryDate);
    }

    public function testStoreSelectedInCartDrivesThePersonalPickupDeliveryDate(): void
    {
        $openStoreStub = $this->createStoreStubOpenEveryDay();
        $selectedStoreStub = $this->createStoreStubOpenEveryDay();

        $internalClosedDayStub = $this->createClosedDayStub(false, [$openStoreStub]);

        $transportStub = $this->createTransportStubDeliveringOnNoSpecialDay(1, true);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getQuantifiedProducts')->willReturn([]);
        $cartStub->method('getTransport')->willReturn($transportStub);
        $cartStub->method('getPickupPlaceIdentifier')->willReturn('selected-store-uuid');

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('findByUuidAndDomainId')
            ->willReturnMap([['selected-store-uuid', Domain::FIRST_DOMAIN_ID, $selectedStoreStub]]);
        $storeFacadeStub->method('getStoresByDomainIdWithEagerLoadedOpeningHours')->willReturn([$openStoreStub, $selectedStoreStub]);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStubWithClosedFriday($internalClosedDayStub),
                $storeFacadeStub,
            )
            ->calculateExpectedDeliveryDate($transportStub, $cartStub, Domain::FIRST_DOMAIN_ID);

        // the selected store is not excluded from the Friday internal day, even though another store
        // is open — the pickup is expected on Saturday
        $this->assertDeliveryDateSame('2026-07-18 00:00:00', $deliveryDate);
    }

    public function testStoreSelectedInCartForAnotherTransportIsIgnored(): void
    {
        $openStoreStub = $this->createStoreStubOpenEveryDay();
        $storeSelectedInCartStub = $this->createStoreStubOpenEveryDay();

        // the store selected in the cart is not excluded from the Friday internal day, but the open store is
        $internalClosedDayStub = $this->createClosedDayStub(false, [$openStoreStub]);

        $transportStub = $this->createTransportStubDeliveringOnNoSpecialDay(1, true);

        $anotherTransportStub = $this->createStub(Transport::class);
        $anotherTransportStub->method('getId')->willReturn(2);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getQuantifiedProducts')->willReturn([]);
        $cartStub->method('getTransport')->willReturn($anotherTransportStub);
        $cartStub->method('getPickupPlaceIdentifier')->willReturn('selected-store-uuid');

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('findByUuidAndDomainId')
            ->willReturnMap([['selected-store-uuid', Domain::FIRST_DOMAIN_ID, $storeSelectedInCartStub]]);
        $storeFacadeStub->method('getStoresByDomainIdWithEagerLoadedOpeningHours')->willReturn([$openStoreStub, $storeSelectedInCartStub]);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStubWithClosedFriday($internalClosedDayStub),
                $storeFacadeStub,
            )
            ->calculateExpectedDeliveryDate($transportStub, $cartStub, Domain::FIRST_DOMAIN_ID);

        // the store selection belongs to another transport, so the best date across all stores applies
        $this->assertDeliveryDateSame('2026-07-17 00:00:00', $deliveryDate);
    }

    #[DataProvider('getClosedDayTypeData')]
    public function testPersonalPickupIgnoresTheClosedDayExemptionsOfTheTransport(bool $isPublicHoliday): void
    {
        $storeStub = $this->createStoreStubOpenEveryDay();

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('getStoresByDomainIdWithEagerLoadedOpeningHours')->willReturn([$storeStub]);

        // the transport claims to deliver on public holidays and internal days, but the store is closed on Friday
        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStubWithClosedFriday($this->createClosedDayStub($isPublicHoliday)),
                $storeFacadeStub,
            )
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringAnyDay(1, true),
                null,
                Domain::FIRST_DOMAIN_ID,
            );

        // the closed Friday blocks the store, so the pickup is expected on Saturday
        $this->assertDeliveryDateSame('2026-07-18 00:00:00', $deliveryDate);
    }

    #[DataProvider('getClosedDayTypeData')]
    public function testStoreExcludedFromTheClosedDayKeepsThePickupDateRegardlessOfTheTransportExemptions(
        bool $isPublicHoliday,
    ): void {
        $excludedStoreStub = $this->createStoreStubOpenEveryDay();

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStubWithClosedFriday(
                    $this->createClosedDayStub($isPublicHoliday, [$excludedStoreStub]),
                ),
            )
            ->calculateExpectedDeliveryDateForStore(
                $this->createTransportStubDeliveringAnyDay(1, true),
                null,
                Domain::FIRST_DOMAIN_ID,
                $excludedStoreStub,
            );

        // the excluded store hands the orders over even on the closed Friday itself
        $this->assertDeliveryDateSame('2026-07-17 00:00:00', $deliveryDate);
    }

    public function testPersonalPickupIgnoresTheDeliveryDaysOfWeekOfTheTransport(): void
    {
        // the transport delivers on working days only, but the store opens on Saturday
        $storeStub = $this->createStoreStubWithOpeningHours([6 => ['18:00']]);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation()->calculateExpectedDeliveryDateForStore(
            $this->createTransportStubDeliveringOnNoSpecialDay(0, true),
            null,
            Domain::FIRST_DOMAIN_ID,
            $storeStub,
        );

        // the pickup is expected on Saturday when the store opens
        $this->assertDeliveryDateSame('2026-07-18 00:00:00', $deliveryDate);
    }

    public function testPersonalPickupIsPostponedToTheNextDayTheStoreIsOpenByOpeningHours(): void
    {
        // NOW is Thursday, the store opens on Mondays only
        $storeStub = $this->createStoreStubWithOpeningHours([1 => ['18:00']]);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation()->calculateExpectedDeliveryDateForStore(
            $this->createTransportStubDeliveringAnyDay(0, true),
            null,
            Domain::FIRST_DOMAIN_ID,
            $storeStub,
        );

        // the pickup is expected on Monday when the store opens
        $this->assertDeliveryDateSame('2026-07-20 00:00:00', $deliveryDate);
    }

    public function testPersonalPickupTodayIsKeptWhileTheStoreIsStillOpen(): void
    {
        // NOW is Thursday 12:00, the store closes at 18:00 today
        $storeStub = $this->createStoreStubWithOpeningHours([4 => ['11:00', '18:00'], 5 => ['18:00']]);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation()->calculateExpectedDeliveryDateForStore(
            $this->createTransportStubDeliveringAnyDay(0, true),
            null,
            Domain::FIRST_DOMAIN_ID,
            $storeStub,
        );

        // the pickup is expected today (Thursday), the store is open until 18:00
        $this->assertDeliveryDateSame('2026-07-16 00:00:00', $deliveryDate);
    }

    public function testPersonalPickupIsPostponedWhenTheStoreHasAlreadyClosedToday(): void
    {
        // NOW is Thursday 12:00, the last range of the store closed at 11:00 today
        $storeStub = $this->createStoreStubWithOpeningHours([4 => ['09:00', '11:00'], 5 => ['18:00']]);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation()->calculateExpectedDeliveryDateForStore(
            $this->createTransportStubDeliveringAnyDay(0, true),
            null,
            Domain::FIRST_DOMAIN_ID,
            $storeStub,
        );

        // the store has already closed today, so the pickup is expected on Friday
        $this->assertDeliveryDateSame('2026-07-17 00:00:00', $deliveryDate);
    }

    public function testPickupDeliveryDateWithoutStoreIsKeptWhenSomeStoreIsStillOpen(): void
    {
        $closedStoreStub = $this->createStoreStubWithOpeningHours([1 => ['18:00']]);
        $openStoreStub = $this->createStoreStubWithOpeningHours([4 => ['18:00']]);

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('getStoresByDomainIdWithEagerLoadedOpeningHours')
            ->willReturn([$closedStoreStub, $openStoreStub]);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation(storeFacade: $storeFacadeStub)
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringAnyDay(0, true),
                null,
                Domain::FIRST_DOMAIN_ID,
            );

        // the second store is open today (Thursday)
        $this->assertDeliveryDateSame('2026-07-16 00:00:00', $deliveryDate);
    }

    public function testPickupDeliveryDateWithoutStoreIsPostponedWhenEveryStoreIsClosedByOpeningHours(): void
    {
        // NOW is Thursday, the stores open on Saturday and Monday respectively
        $saturdayStoreStub = $this->createStoreStubWithOpeningHours([6 => ['11:00']]);
        $mondayStoreStub = $this->createStoreStubWithOpeningHours([1 => ['18:00']]);

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('getStoresByDomainIdWithEagerLoadedOpeningHours')
            ->willReturn([$saturdayStoreStub, $mondayStoreStub]);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation(storeFacade: $storeFacadeStub)
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringAnyDay(0, true),
                null,
                Domain::FIRST_DOMAIN_ID,
            );

        // Saturday is the first day some store opens
        $this->assertDeliveryDateSame('2026-07-18 00:00:00', $deliveryDate);
    }

    public function testBestPickupDateIsTheEarliestDateOfTheIndividualStores(): void
    {
        // NOW is Thursday 12:00; the store having the goods in stock has already closed today,
        // while the store still open today waits for a 3 days transfer
        $stockedClosedStoreStub = $this->createStoreStubWithOpeningHours([4 => ['11:00'], 5 => ['18:00']]);
        $openTransferStoreStub = $this->createStoreStubOpenEveryDay();

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('getStoresByDomainIdWithEagerLoadedOpeningHours')
            ->willReturn([$stockedClosedStoreStub, $openTransferStoreStub]);

        $productAvailabilityFacadeStub = $this->createTransferProductAvailabilityFacadeStub(
            static fn (Store $store): bool => $store !== $stockedClosedStoreStub,
        );

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                storeFacade: $storeFacadeStub,
                productAvailabilityFacade: $productAvailabilityFacadeStub,
            )
            ->calculateExpectedDeliveryDateForProduct(
                $this->createTransportStubDeliveringAnyDay(0, true),
                $this->createProductStub(),
                Domain::FIRST_DOMAIN_ID,
            );

        // the earliest pickup is Friday at the stocked store — neither today (already closed),
        // nor Sunday at the open store after the transfer
        $this->assertDeliveryDateSame('2026-07-17 00:00:00', $deliveryDate);
    }

    public function testPickupDeliveryDateWithoutAnyStoreOnTheDomainIsNull(): void
    {
        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                productAvailabilityFacade: $this->createTransferProductAvailabilityFacadeStub(static fn (): bool => false),
            )
            ->calculateExpectedDeliveryDateForProduct(
                $this->createTransportStubDeliveringAnyDay(0, true),
                $this->createProductStub(),
                Domain::FIRST_DOMAIN_ID,
            );

        $this->assertDeliveryDateSame(null, $deliveryDate);
    }

    public function testStoreWithoutAnyConfiguredOpeningHoursNeverPromisesPickupDate(): void
    {
        // the storefront presents such a store as permanently closed, so no pickup date is promised either
        $storeStub = $this->createStub(Store::class);
        $storeStub->method('getOpeningHours')->willReturn([]);

        $deliveryDate = $this->createTransportExpectedDeliveryDateCalculation()->calculateExpectedDeliveryDateForStore(
            $this->createTransportStubDeliveringAnyDay(0, true),
            null,
            Domain::FIRST_DOMAIN_ID,
            $storeStub,
        );

        $this->assertDeliveryDateSame(null, $deliveryDate);
    }

    /**
     * Creates a store whose weekly opening hours contain a row for every day of the week,
     * with ranges closing at the given times on the given ISO days (opening at 08:00)
     *
     * @param array<int, string[]> $closingTimesByDayOfWeek
     */
    private function createStoreStubWithOpeningHours(array $closingTimesByDayOfWeek, ?int $id = null): Store
    {
        $weekOpeningHours = [];

        foreach (DateTimeHelper::ALL_DAYS_OF_WEEK as $dayOfWeek) {
            $openingHoursRanges = [];

            foreach ($closingTimesByDayOfWeek[$dayOfWeek] ?? [] as $closingTime) {
                $openingHoursRangeStub = $this->createStub(OpeningHoursRange::class);
                $openingHoursRangeStub->method('getOpeningTime')->willReturn('08:00');
                $openingHoursRangeStub->method('getClosingTime')->willReturn($closingTime);
                $openingHoursRanges[] = $openingHoursRangeStub;
            }

            $openingHoursStub = $this->createStub(OpeningHours::class);
            $openingHoursStub->method('getDayOfWeek')->willReturn($dayOfWeek);
            $openingHoursStub->method('getOpeningHoursRanges')->willReturn($openingHoursRanges);
            $weekOpeningHours[] = $openingHoursStub;
        }

        $storeStub = $this->createStub(Store::class);
        $storeStub->method('getOpeningHours')->willReturn($weekOpeningHours);

        if ($id !== null) {
            $storeStub->method('getId')->willReturn($id);
        }

        return $storeStub;
    }

    private function createStoreStubOpenEveryDay(?int $id = null): Store
    {
        return $this->createStoreStubWithOpeningHours(
            array_fill_keys(DateTimeHelper::ALL_DAYS_OF_WEEK, ['23:59']),
            $id,
        );
    }

    public function testCalculationForStoreRejectsNonPersonalPickupTransport(): void
    {
        $this->expectException(TransportIsNotPersonalPickupException::class);

        $this->createTransportExpectedDeliveryDateCalculation()->calculateExpectedDeliveryDateForStore(
            $this->createTransportStubDeliveringAnyDay(self::DAYS_UNTIL_DELIVERY),
            null,
            Domain::FIRST_DOMAIN_ID,
            $this->createStoreStubOpenEveryDay(),
        );
    }

    /**
     * @return iterable<string, array{stockQuantity: int, expectedRestockingDate: string|null, expectedDeliveryDate: string|null}>
     */
    public static function getExpectedDeliveryDateForProductData(): iterable
    {
        yield 'standard delivery date for a product in stock' => [
            'stockQuantity' => 10,
            'expectedRestockingDate' => null,
            'expectedDeliveryDate' => self::STANDARD_DELIVERY_DATE,
        ];

        yield 'sold-out product with a restocking date postpones the delivery' => [
            'stockQuantity' => 0,
            'expectedRestockingDate' => self::RESTOCKING_DATE,
            'expectedDeliveryDate' => self::DELIVERY_DATE_AFTER_RESTOCKING,
        ];

        yield 'null for a sold-out product without a valid restocking date' => [
            'stockQuantity' => 0,
            'expectedRestockingDate' => null,
            'expectedDeliveryDate' => null,
        ];
    }

    #[DataProvider('getExpectedDeliveryDateForProductData')]
    public function testExpectedDeliveryDateIsDerivedFromSinglePieceOfProduct(
        int $stockQuantity,
        ?string $expectedRestockingDate,
        ?string $expectedDeliveryDate,
    ): void {
        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId')
            ->willReturn([1 => $stockQuantity]);
        $productAvailabilityFacadeStub->method('findValidExpectedRestockingDate')
            ->willReturn($expectedRestockingDate === null ? null : new DatePoint($expectedRestockingDate, new DateTimeZone('UTC')));

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(productAvailabilityFacade: $productAvailabilityFacadeStub)
            ->calculateExpectedDeliveryDateForProduct(
                $this->createTransportStubDeliveringAnyDay(self::DAYS_UNTIL_DELIVERY),
                $this->createProductStub(),
                Domain::FIRST_DOMAIN_ID,
            );

        $this->assertDeliveryDateSame($expectedDeliveryDate, $deliveryDate);
    }

    /**
     * @return iterable<string, array{isTransferToStoreNeeded: bool, expectedDeliveryDate: string}>
     */
    public static function getExpectedDeliveryDateForStoreTransferData(): iterable
    {
        yield 'store stock covers the product quantity, so transfer between stocks is not included in the calculation' => [
            'isTransferToStoreNeeded' => false,
            'expectedDeliveryDate' => '2026-07-16 00:00:00',
        ];

        yield 'store stock does not cover the product quantity, so transfer between stocks is included in the calculation' => [
            'isTransferToStoreNeeded' => true,
            'expectedDeliveryDate' => '2026-07-19 00:00:00',
        ];
    }

    #[DataProvider('getExpectedDeliveryDateForStoreTransferData')]
    public function testProductDeliveryDateForStoreIsPostponedByTransferDaysWhenStoreStockDoesNotCoverTheQuantity(
        bool $isTransferToStoreNeeded,
        string $expectedDeliveryDate,
    ): void {
        $storeStub = $this->createStoreStubOpenEveryDay(1);

        $productAvailabilityFacadeStub = $this->createTransferProductAvailabilityFacadeStub(
            static fn (): bool => $isTransferToStoreNeeded,
        );

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(productAvailabilityFacade: $productAvailabilityFacadeStub)
            ->calculateExpectedDeliveryDateForStoreAndProduct(
                $this->createTransportStubDeliveringAnyDay(0, true),
                $this->createProductStub(),
                Domain::FIRST_DOMAIN_ID,
                $storeStub,
            );

        $this->assertDeliveryDateSame($expectedDeliveryDate, $deliveryDate);
    }

    /**
     * @return iterable<string, array{someStoreCoversTheProductQuantity: bool, expectedDeliveryDate: string}>
     */
    public static function getBestPickupDeliveryDateTransferData(): iterable
    {
        yield 'some store covers the product quantity from its own stock, so transfer between stocks is not included in the calculation' => [
            'someStoreCoversTheProductQuantity' => true,
            'expectedDeliveryDate' => '2026-07-16 00:00:00',
        ];

        yield 'no store covers the product quantity from its own stock, so transfer between stocks is included in the calculation' => [
            'someStoreCoversTheProductQuantity' => false,
            'expectedDeliveryDate' => '2026-07-19 00:00:00',
        ];
    }

    #[DataProvider('getBestPickupDeliveryDateTransferData')]
    public function testPickupDeliveryDateWithoutStoreIsPostponedByTransferDaysWhenNoStoreStockCoversTheQuantity(
        bool $someStoreCoversTheProductQuantity,
        string $expectedDeliveryDate,
    ): void {
        $storeWithoutStockStub = $this->createStoreStubOpenEveryDay();
        $coveringStoreStub = $this->createStoreStubOpenEveryDay();

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('getStoresByDomainIdWithEagerLoadedOpeningHours')->willReturn([$storeWithoutStockStub, $coveringStoreStub]);

        $productAvailabilityFacadeStub = $this->createTransferProductAvailabilityFacadeStub(
            static fn (Store $store): bool => $store !== $coveringStoreStub || !$someStoreCoversTheProductQuantity,
        );

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                storeFacade: $storeFacadeStub,
                productAvailabilityFacade: $productAvailabilityFacadeStub,
            )
            ->calculateExpectedDeliveryDateForProduct(
                $this->createTransportStubDeliveringAnyDay(0, true),
                $this->createProductStub(),
                Domain::FIRST_DOMAIN_ID,
            );

        $this->assertDeliveryDateSame($expectedDeliveryDate, $deliveryDate);
    }

    public function testDeliveryDateOfNonPickupTransportIsNeverPostponedByTransferDays(): void
    {
        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('getStoresByDomainIdWithEagerLoadedOpeningHours')->willReturn([$this->createStoreStubOpenEveryDay()]);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                storeFacade: $storeFacadeStub,
                productAvailabilityFacade: $this->createTransferProductAvailabilityFacadeStub(static fn (): bool => true),
            )
            ->calculateExpectedDeliveryDateForProduct(
                $this->createTransportStubDeliveringAnyDay(0),
                $this->createProductStub(),
                Domain::FIRST_DOMAIN_ID,
            );

        $this->assertDeliveryDateSame('2026-07-16 00:00:00', $deliveryDate);
    }

    /**
     * Creates a facade stub resolving the single product created by createProductStub() as stocked
     * on the domain, with the given callback deciding the transfer need per store and 3 transfer days
     *
     * @param callable(\Shopsys\FrameworkBundle\Model\Store\Store): bool $isTransferToStoreNeededResolver
     */
    private function createTransferProductAvailabilityFacadeStub(
        callable $isTransferToStoreNeededResolver,
        int $stockQuantity = 1,
        ?string $expectedRestockingDate = null,
    ): ProductAvailabilityFacade {
        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId')
            ->willReturn([1 => $stockQuantity]);
        $productAvailabilityFacadeStub->method('findValidExpectedRestockingDate')
            ->willReturn($expectedRestockingDate === null ? null : new DatePoint($expectedRestockingDate, new DateTimeZone('UTC')));
        $productAvailabilityFacadeStub->method('isTransferToStoreNeeded')
            ->willReturnCallback(
                static fn (array $quantifiedProducts, Store $store): bool => $isTransferToStoreNeededResolver($store),
            );
        $productAvailabilityFacadeStub->method('getTransferDaysByDomainId')
            ->willReturn(3);

        return $productAvailabilityFacadeStub;
    }

    public function testProductDeliveryDateForStoreIncludesTransferDaysAfterRestocking(): void
    {
        $productAvailabilityFacadeStub = $this->createTransferProductAvailabilityFacadeStub(
            static fn (): bool => true,
            stockQuantity: 0,
            expectedRestockingDate: self::RESTOCKING_DATE,
        );

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(productAvailabilityFacade: $productAvailabilityFacadeStub)
            ->calculateExpectedDeliveryDateForStoreAndProduct(
                $this->createTransportStubDeliveringAnyDay(0, true),
                $this->createProductStub(),
                Domain::FIRST_DOMAIN_ID,
                $this->createStoreStubOpenEveryDay(),
            );

        // RESTOCKING_DATE + 3 transfer days
        $this->assertDeliveryDateSame('2026-07-29 00:00:00', $deliveryDate);
    }

    public function testProductCalculationForStoreRejectsNonPersonalPickupTransport(): void
    {
        $this->expectException(TransportIsNotPersonalPickupException::class);

        $this->createTransportExpectedDeliveryDateCalculation()->calculateExpectedDeliveryDateForStoreAndProduct(
            $this->createTransportStubDeliveringAnyDay(self::DAYS_UNTIL_DELIVERY),
            $this->createStub(Product::class),
            Domain::FIRST_DOMAIN_ID,
            $this->createStoreStubOpenEveryDay(),
        );
    }

    #[DataProvider('getClosedDayTypeData')]
    public function testProductDeliveryDateForStoreIsPostponedByTheClosedDayOfTheGivenStore(
        bool $isPublicHoliday,
    ): void {
        $openStoreStub = $this->createStoreStubOpenEveryDay();
        $givenStoreStub = $this->createStoreStubOpenEveryDay();

        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId')
            ->willReturn([1 => 10]);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStubWithClosedFriday(
                    $this->createClosedDayStub($isPublicHoliday, [$openStoreStub]),
                ),
                productAvailabilityFacade: $productAvailabilityFacadeStub,
            )
            ->calculateExpectedDeliveryDateForStoreAndProduct(
                $this->createTransportStubDeliveringOnNoSpecialDay(1, true),
                $this->createProductStub(),
                Domain::FIRST_DOMAIN_ID,
                $givenStoreStub,
            );

        // the given store is not excluded from the Friday closed day, even though another store
        // is open — the pickup is expected on Saturday
        $this->assertDeliveryDateSame('2026-07-18 00:00:00', $deliveryDate);
    }

    public function testCalculationForStoreIgnoresTheStoreSelectedInCart(): void
    {
        $storeSelectedInCartStub = $this->createStoreStubOpenEveryDay();
        $explicitStoreStub = $this->createStoreStubOpenEveryDay();

        // only the store selected in the cart is excluded from the Friday internal day
        $internalClosedDayStub = $this->createClosedDayStub(false, [$storeSelectedInCartStub]);

        $transportStub = $this->createTransportStubDeliveringOnNoSpecialDay(1, true);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getQuantifiedProducts')->willReturn([]);
        $cartStub->method('getTransport')->willReturn($transportStub);
        $cartStub->method('getPickupPlaceIdentifier')->willReturn('selected-store-uuid');

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('findByUuidAndDomainId')
            ->willReturnMap([['selected-store-uuid', Domain::FIRST_DOMAIN_ID, $storeSelectedInCartStub]]);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStubWithClosedFriday($internalClosedDayStub),
                $storeFacadeStub,
            )
            ->calculateExpectedDeliveryDateForStore($transportStub, $cartStub, Domain::FIRST_DOMAIN_ID, $explicitStoreStub);

        // the date of the explicitly given store is postponed even though the store selected in the cart
        // is open — the pickup is expected on Saturday
        $this->assertDeliveryDateSame('2026-07-18 00:00:00', $deliveryDate);
    }

    /**
     * Calculates the delivery date of a personal pickup transport on Friday 2026-07-17,
     * which is a closed day of the e-shop
     *
     * @param \Shopsys\FrameworkBundle\Model\Store\Store[] $storesOnDomain
     */
    private function calculatePersonalPickupDeliveryDateOnClosedFriday(
        ClosedDay $closedDay,
        array $storesOnDomain,
        ?Store $selectedStore = null,
    ): ?DateTimeImmutable {
        $transportStub = $this->createTransportStubDeliveringOnNoSpecialDay(1, true);

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('getStoresByDomainIdWithEagerLoadedOpeningHours')->willReturn($storesOnDomain);

        $transportExpectedDeliveryDateCalculation = $this->createTransportExpectedDeliveryDateCalculation(
            $this->createClosedDayFacadeStubWithClosedFriday($closedDay),
            $storeFacadeStub,
        );

        if ($selectedStore === null) {
            return $transportExpectedDeliveryDateCalculation
                ->calculateExpectedDeliveryDate($transportStub, null, Domain::FIRST_DOMAIN_ID);
        }

        return $transportExpectedDeliveryDateCalculation
            ->calculateExpectedDeliveryDateForStore($transportStub, null, Domain::FIRST_DOMAIN_ID, $selectedStore);
    }

    private function createClosedDayFacadeStubWithClosedFriday(ClosedDay $closedDay): ClosedDayFacade
    {
        $closedDayFacadeStub = $this->createStub(ClosedDayFacade::class);
        $closedDayFacadeStub->method('getClosedDaysWithEagerLoadedExcludedStores')->willReturn([$closedDay]);

        return $closedDayFacadeStub;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store[] $excludedStores
     */
    private function createClosedDayStub(
        bool $isPublicHoliday,
        array $excludedStores = [],
        string $date = '2026-07-17',
    ): ClosedDay {
        $closedDayStub = $this->createStub(ClosedDay::class);
        $closedDayStub->method('isPublicHoliday')->willReturn($isPublicHoliday);
        $closedDayStub->method('getExcludedStores')->willReturn($excludedStores);
        $closedDayStub->method('getDate')->willReturn(new DatePoint($date, new DateTimeZone('UTC')));

        return $closedDayStub;
    }

    /**
     * @param string[] $publicHolidays
     * @param string[] $internalClosedDays
     */
    private function createClosedDayFacadeStub(array $publicHolidays, array $internalClosedDays): ClosedDayFacade
    {
        $closedDays = [
            ...array_map(fn (string $date): ClosedDay => $this->createClosedDayStub(true, [], $date), $publicHolidays),
            ...array_map(fn (string $date): ClosedDay => $this->createClosedDayStub(false, [], $date), $internalClosedDays),
        ];

        $closedDayFacadeStub = $this->createStub(ClosedDayFacade::class);
        $closedDayFacadeStub->method('getClosedDaysWithEagerLoadedExcludedStores')->willReturn($closedDays);

        return $closedDayFacadeStub;
    }

    private function createTransportExpectedDeliveryDateCalculation(
        ?ClosedDayFacade $closedDayFacade = null,
        ?StoreFacade $storeFacade = null,
        ?ProductAvailabilityFacade $productAvailabilityFacade = null,
        string $now = self::NOW,
        string $displayTimezone = 'UTC',
    ): TransportExpectedDeliveryDateCalculation {
        $clockStub = $this->createStub(ClockInterface::class);
        $clockStub->method('now')->willReturn(new DatePoint($now, new DateTimeZone('UTC')));

        $displayTimeZoneProviderStub = $this->createStub(DisplayTimeZoneProviderInterface::class);
        $displayTimeZoneProviderStub->method('getDisplayTimeZoneByDomainId')->willReturn(new DateTimeZone($displayTimezone));

        return new TransportExpectedDeliveryDateCalculation(
            $productAvailabilityFacade ?? $this->createStub(ProductAvailabilityFacade::class),
            $clockStub,
            $displayTimeZoneProviderStub,
            $closedDayFacade ?? $this->createStub(ClosedDayFacade::class),
            $storeFacade ?? $this->createStub(StoreFacade::class),
            new InMemoryCache(),
            new StoreOpeningHoursProvider($this->createStub(ClosedDayFacade::class), new InMemoryCache()),
        );
    }

    private function createTransportStubDeliveringAnyDay(
        int $daysUntilDelivery,
        bool $isPersonalPickup = false,
    ): Transport {
        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getDaysUntilDelivery')->willReturn($daysUntilDelivery);
        $transportStub->method('isPersonalPickup')->willReturn($isPersonalPickup);
        $this->stubDeliveryDaysOfWeek($transportStub, DateTimeHelper::ALL_DAYS_OF_WEEK);
        $transportStub->method('deliversOnPublicHolidays')->willReturn(true);
        $transportStub->method('deliversOnInternalClosedDays')->willReturn(true);

        return $transportStub;
    }

    private function createTransportStubDeliveringOnNoSpecialDay(
        int $daysUntilDelivery,
        bool $isPersonalPickup = false,
    ): Transport {
        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getId')->willReturn(1);
        $transportStub->method('getDaysUntilDelivery')->willReturn($daysUntilDelivery);
        $transportStub->method('isPersonalPickup')->willReturn($isPersonalPickup);
        $this->stubDeliveryDaysOfWeek($transportStub, DateTimeHelper::WORKING_DAYS_OF_WEEK);
        $transportStub->method('deliversOnPublicHolidays')->willReturn(false);
        $transportStub->method('deliversOnInternalClosedDays')->willReturn(false);

        return $transportStub;
    }

    /**
     * @param int[] $deliveryDaysOfWeek
     */
    private function stubDeliveryDaysOfWeek(Stub&Transport $transportStub, array $deliveryDaysOfWeek): void
    {
        $transportStub->method('deliversOnDayOfWeek')->willReturnCallback(
            static fn (int $dayOfWeek): bool => in_array($dayOfWeek, $deliveryDaysOfWeek, true),
        );
    }

    private function createProductStub(): Product
    {
        $productStub = $this->createStub(Product::class);
        $productStub->method('getId')->willReturn(1);

        return $productStub;
    }

    private function createCartStubWithSingleProduct(): Cart
    {
        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getQuantifiedProducts')->willReturn([new QuantifiedProduct($this->createProductStub(), 1)]);

        return $cartStub;
    }

    /**
     * Creates a facade stub resolving the single product created by createProductStub() as sold out
     */
    private function createSoldOutProductAvailabilityFacadeStub(
        string $expectedRestockingDate,
    ): ProductAvailabilityFacade {
        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId')
            ->willReturn([1 => 0]);
        $productAvailabilityFacadeStub->method('findValidExpectedRestockingDate')
            ->willReturn(new DatePoint($expectedRestockingDate, new DateTimeZone('UTC')));

        return $productAvailabilityFacadeStub;
    }

    private function assertDeliveryDateSame(?string $expectedDeliveryDate, ?DateTimeImmutable $deliveryDate): void
    {
        if ($deliveryDate !== null) {
            $this->assertSame('UTC', $deliveryDate->getTimezone()->getName());
        }

        $this->assertSame($expectedDeliveryDate, $deliveryDate?->format('Y-m-d H:i:s'));
    }
}
