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
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicesDeliveryDaysExtensionCalculation;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
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
        $cartItemStubs = [];

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

            $cartItemStub = $this->createStub(CartItem::class);
            $cartItemStub->method('getProduct')->willReturn($productStub);
            $cartItemStub->method('getQuantity')->willReturn($cartItemData['quantity']);
            $cartItemStub->method('getAdditionalServices')->willReturn([]);
            $cartItemStubs[] = $cartItemStub;
        }

        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId')
            ->willReturn($stockQuantitiesIndexedByProductId);
        $productAvailabilityFacadeStub->method('findValidExpectedRestockingDate')
            ->willReturnMap($restockingDateMap);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn($cartItemStubs);

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
        $productStub = $this->createStub(Product::class);
        $productStub->method('getId')->willReturn(1);

        $cartItemStub = $this->createStub(CartItem::class);
        $cartItemStub->method('getProduct')->willReturn($productStub);
        $cartItemStub->method('getQuantity')->willReturn(1);
        $cartItemStub->method('getAdditionalServices')->willReturn([]);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn([$cartItemStub]);

        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId')->willReturn([1 => 0]);
        $productAvailabilityFacadeStub->method('findValidExpectedRestockingDate')
            ->willReturn(new DatePoint(self::RESTOCKING_DATE, new DateTimeZone('UTC')));

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(productAvailabilityFacade: $productAvailabilityFacadeStub)
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringOnNoSpecialDay(0),
                $cartStub,
                Domain::FIRST_DOMAIN_ID,
            );

        $this->assertDeliveryDateSame('2026-07-27 00:00:00', $deliveryDate);
    }

    public function testRestockingDateIsDeterminedInTheDisplayTimeZone(): void
    {
        // 2026-07-16 22:00 UTC is already Friday 2026-07-17 in Europe/Prague, which is a closed day of the e-shop
        $productStub = $this->createStub(Product::class);
        $productStub->method('getId')->willReturn(1);

        $cartItemStub = $this->createStub(CartItem::class);
        $cartItemStub->method('getProduct')->willReturn($productStub);
        $cartItemStub->method('getQuantity')->willReturn(1);
        $cartItemStub->method('getAdditionalServices')->willReturn([]);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn([$cartItemStub]);

        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId')->willReturn([1 => 0]);
        $productAvailabilityFacadeStub->method('findValidExpectedRestockingDate')
            ->willReturn(new DatePoint('2026-07-16 22:00:00', new DateTimeZone('UTC')));

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStub([], ['2026-07-17']),
                productAvailabilityFacade: $productAvailabilityFacadeStub,
                displayTimezone: 'Europe/Prague',
            )
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringOnNoSpecialDay(0),
                $cartStub,
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
     * @return iterable<string, array{daysUntilDelivery: int, deliveryDaysExtensions: int[], publicHolidays: string[], expectedDeliveryDate: string}>
     */
    public static function getDeliveryDateWithAdditionalServicesExtensionData(): iterable
    {
        yield 'extension moves the delivery postponed from the weekend by a whole working day' => [
            'daysUntilDelivery' => 2,
            'deliveryDaysExtensions' => [1],
            'publicHolidays' => [],
            'expectedDeliveryDate' => '2026-07-21 00:00:00',
        ];

        yield 'extension steps over the weekend' => [
            'daysUntilDelivery' => 1,
            'deliveryDaysExtensions' => [1],
            'publicHolidays' => [],
            'expectedDeliveryDate' => '2026-07-20 00:00:00',
        ];

        yield 'extension steps over a public holiday' => [
            'daysUntilDelivery' => 1,
            'deliveryDaysExtensions' => [1],
            'publicHolidays' => ['2026-07-20'],
            'expectedDeliveryDate' => '2026-07-21 00:00:00',
        ];

        yield 'the highest extension of all chosen services applies' => [
            'daysUntilDelivery' => 1,
            'deliveryDaysExtensions' => [1, 2],
            'publicHolidays' => [],
            'expectedDeliveryDate' => '2026-07-21 00:00:00',
        ];
    }

    /**
     * @param int[] $deliveryDaysExtensions
     * @param string[] $publicHolidays
     */
    #[DataProvider('getDeliveryDateWithAdditionalServicesExtensionData')]
    public function testAdditionalServicesExtendTheDeliveryDateByWholeAllowedDeliveryDays(
        int $daysUntilDelivery,
        array $deliveryDaysExtensions,
        array $publicHolidays,
        string $expectedDeliveryDate,
    ): void {
        $cartStub = $this->createCartStubWithAdditionalServices($deliveryDaysExtensions);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStub($publicHolidays, []),
                productAvailabilityFacade: $this->createProductAvailabilityFacadeStubWithStockedProduct(),
            )
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringOnNoSpecialDay($daysUntilDelivery),
                $cartStub,
                Domain::FIRST_DOMAIN_ID,
            );

        $this->assertDeliveryDateSame($expectedDeliveryDate, $deliveryDate);
    }

    public function testAdditionalServicesExtensionCountsTheDaysTheTransportDeliversOn(): void
    {
        $cartStub = $this->createCartStubWithAdditionalServices([1]);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                productAvailabilityFacade: $this->createProductAvailabilityFacadeStubWithStockedProduct(),
            )
            ->calculateExpectedDeliveryDate(
                $this->createTransportStubDeliveringAnyDay(1),
                $cartStub,
                Domain::FIRST_DOMAIN_ID,
            );

        $this->assertDeliveryDateSame('2026-07-18 00:00:00', $deliveryDate);
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
        $openStoreStub = $this->createStub(Store::class);
        $closedStoreStub = $this->createStub(Store::class);

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
        $storeStub = $this->createStub(Store::class);

        $deliveryDate = $this->calculatePersonalPickupDeliveryDateOnClosedFriday(
            $this->createClosedDayStub($isPublicHoliday),
            [$storeStub],
        );

        // Friday is blocked by the closed day, the weekend by the delivery days configuration
        $this->assertDeliveryDateSame('2026-07-20 00:00:00', $deliveryDate);
    }

    #[DataProvider('getClosedDayTypeData')]
    public function testSelectedStoreDeliveryDateIsPostponedEvenWhenAnotherStoreIsOpen(
        bool $isPublicHoliday,
    ): void {
        $openStoreStub = $this->createStub(Store::class);
        $selectedStoreStub = $this->createStub(Store::class);

        $deliveryDate = $this->calculatePersonalPickupDeliveryDateOnClosedFriday(
            $this->createClosedDayStub($isPublicHoliday, [$openStoreStub]),
            [$selectedStoreStub, $openStoreStub],
            $selectedStoreStub,
        );

        $this->assertDeliveryDateSame('2026-07-20 00:00:00', $deliveryDate);
    }

    #[DataProvider('getClosedDayTypeData')]
    public function testSelectedStoreDeliveryDateIsKeptWhenTheStoreIsExcludedFromTheClosedDay(
        bool $isPublicHoliday,
    ): void {
        $selectedStoreStub = $this->createStub(Store::class);
        $closedStoreStub = $this->createStub(Store::class);

        $deliveryDate = $this->calculatePersonalPickupDeliveryDateOnClosedFriday(
            $this->createClosedDayStub($isPublicHoliday, [$selectedStoreStub]),
            [$selectedStoreStub, $closedStoreStub],
            $selectedStoreStub,
        );

        $this->assertDeliveryDateSame('2026-07-17 00:00:00', $deliveryDate);
    }

    public function testStoreSelectedInCartDrivesThePersonalPickupDeliveryDate(): void
    {
        $openStoreStub = $this->createStub(Store::class);
        $selectedStoreStub = $this->createStub(Store::class);

        $internalClosedDayStub = $this->createClosedDayStub(false, [$openStoreStub]);

        $transportStub = $this->createTransportStubDeliveringOnNoSpecialDay(1, true);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn([]);
        $cartStub->method('getTransport')->willReturn($transportStub);
        $cartStub->method('getPickupPlaceIdentifier')->willReturn('selected-store-uuid');

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('findByUuidAndDomainId')
            ->willReturnMap([['selected-store-uuid', Domain::FIRST_DOMAIN_ID, $selectedStoreStub]]);
        $storeFacadeStub->method('getStoresByDomainId')->willReturn([$openStoreStub, $selectedStoreStub]);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStubWithClosedFriday($internalClosedDayStub),
                $storeFacadeStub,
            )
            ->calculateExpectedDeliveryDate($transportStub, $cartStub, Domain::FIRST_DOMAIN_ID);

        // the selected store is not excluded from the Friday internal day, even though another store is open
        $this->assertDeliveryDateSame('2026-07-20 00:00:00', $deliveryDate);
    }

    public function testStoreSelectedInCartForAnotherTransportIsIgnored(): void
    {
        $openStoreStub = $this->createStub(Store::class);
        $storeSelectedInCartStub = $this->createStub(Store::class);

        // the store selected in the cart is not excluded from the Friday internal day, but the open store is
        $internalClosedDayStub = $this->createClosedDayStub(false, [$openStoreStub]);

        $transportStub = $this->createTransportStubDeliveringOnNoSpecialDay(1, true);

        $anotherTransportStub = $this->createStub(Transport::class);
        $anotherTransportStub->method('getId')->willReturn(2);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn([]);
        $cartStub->method('getTransport')->willReturn($anotherTransportStub);
        $cartStub->method('getPickupPlaceIdentifier')->willReturn('selected-store-uuid');

        $storeFacadeStub = $this->createStub(StoreFacade::class);
        $storeFacadeStub->method('findByUuidAndDomainId')
            ->willReturnMap([['selected-store-uuid', Domain::FIRST_DOMAIN_ID, $storeSelectedInCartStub]]);
        $storeFacadeStub->method('getStoresByDomainId')->willReturn([$openStoreStub, $storeSelectedInCartStub]);

        $deliveryDate = $this
            ->createTransportExpectedDeliveryDateCalculation(
                $this->createClosedDayFacadeStubWithClosedFriday($internalClosedDayStub),
                $storeFacadeStub,
            )
            ->calculateExpectedDeliveryDate($transportStub, $cartStub, Domain::FIRST_DOMAIN_ID);

        // the store selection belongs to another transport, so the best date across all stores applies
        $this->assertDeliveryDateSame('2026-07-17 00:00:00', $deliveryDate);
    }

    public function testCalculationForStoreRejectsNonPersonalPickupTransport(): void
    {
        $this->expectException(TransportIsNotPersonalPickupException::class);

        $this->createTransportExpectedDeliveryDateCalculation()->calculateExpectedDeliveryDateForStore(
            $this->createTransportStubDeliveringAnyDay(self::DAYS_UNTIL_DELIVERY),
            null,
            Domain::FIRST_DOMAIN_ID,
            $this->createStub(Store::class),
        );
    }

    public function testCalculationForStoreIgnoresTheStoreSelectedInCart(): void
    {
        $storeSelectedInCartStub = $this->createStub(Store::class);
        $explicitStoreStub = $this->createStub(Store::class);

        // only the store selected in the cart is excluded from the Friday internal day
        $internalClosedDayStub = $this->createClosedDayStub(false, [$storeSelectedInCartStub]);

        $transportStub = $this->createTransportStubDeliveringOnNoSpecialDay(1, true);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn([]);
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

        // the date of the explicitly given store is postponed even though the store selected in the cart is open
        $this->assertDeliveryDateSame('2026-07-20 00:00:00', $deliveryDate);
    }

    /**
     * Calculates the delivery date of a personal pickup transport delivering on Friday 2026-07-17,
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
        $storeFacadeStub->method('getStoresByDomainId')->willReturn($storesOnDomain);

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

    /**
     * @param int[] $deliveryDaysExtensions
     */
    private function createCartStubWithAdditionalServices(array $deliveryDaysExtensions): Cart
    {
        $additionalServiceStubs = array_map(function (int $deliveryDaysExtension): AdditionalService {
            $additionalServiceStub = $this->createStub(AdditionalService::class);
            $additionalServiceStub->method('getDeliveryDaysExtension')->willReturn($deliveryDaysExtension);

            return $additionalServiceStub;
        }, $deliveryDaysExtensions);

        $productStub = $this->createStub(Product::class);
        $productStub->method('getId')->willReturn(1);

        $cartItemStub = $this->createStub(CartItem::class);
        $cartItemStub->method('getProduct')->willReturn($productStub);
        $cartItemStub->method('getQuantity')->willReturn(1);
        $cartItemStub->method('getAdditionalServices')->willReturn($additionalServiceStubs);

        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getItems')->willReturn([$cartItemStub]);

        return $cartStub;
    }

    private function createProductAvailabilityFacadeStubWithStockedProduct(): ProductAvailabilityFacade
    {
        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId')
            ->willReturn([1 => 10]);

        return $productAvailabilityFacadeStub;
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
            new AdditionalServicesDeliveryDaysExtensionCalculation(),
        );
    }

    private function createTransportStubDeliveringAnyDay(int $daysUntilDelivery): Transport
    {
        $transportStub = $this->createStub(Transport::class);
        $transportStub->method('getDaysUntilDelivery')->willReturn($daysUntilDelivery);
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

    private function assertDeliveryDateSame(?string $expectedDeliveryDate, ?DateTimeImmutable $deliveryDate): void
    {
        if ($deliveryDate !== null) {
            $this->assertSame('UTC', $deliveryDate->getTimezone()->getName());
        }

        $this->assertSame($expectedDeliveryDate, $deliveryDate?->format('Y-m-d H:i:s'));
    }
}
