<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Availability;

use DateTimeImmutable;
use DateTimeZone;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityStatusEnum;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

/**
 * The display timezone is set to UTC in these tests, so all date strings can be read literally
 * with no timezone conversion in mind. The only exception is the dedicated
 * testDateValidityIsEvaluatedInDisplayTimezone(), which covers the display timezone handling itself.
 */
final class ProductAvailabilityFacadeTest extends TestCase
{
    private const string FORMATTED_DATE = 'Jul 20, 2026';

    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')
            ->willReturnCallback(
                static fn (string $id, array $parameters = []): string => strtr($id, $parameters),
            );
        Translator::injectSelf($translatorStub);
    }

    /**
     * @return iterable<string, array{now: string, expectedRestockingDate: string}>
     */
    public static function getEffectiveExpectedRestockingDateData(): iterable
    {
        yield 'date in the future' => [
            'now' => '2026-07-12 12:00:00',
            'expectedRestockingDate' => '2026-07-22 00:00:00',
        ];

        yield 'date still valid throughout the whole restocking day' => [
            'now' => '2026-07-12 22:00:00',
            'expectedRestockingDate' => '2026-07-12 00:00:00',
        ];
    }

    #[DataProvider('getEffectiveExpectedRestockingDateData')]
    public function testEffectiveExpectedRestockingDateIsReturnedWhenOutOfStockWithValidDate(
        string $now,
        string $expectedRestockingDate,
    ): void {
        $restockingDate = $this->createDate($expectedRestockingDate);
        $productAvailabilityFacade = $this->createProductAvailabilityFacade(false, $now);
        $product = $this->createProduct($restockingDate);

        $effectiveDate = $productAvailabilityFacade->findEffectiveExpectedRestockingDate($product, Domain::FIRST_DOMAIN_ID);

        $this->assertSame($restockingDate, $effectiveDate);
    }

    /**
     * @return iterable<string, array{now: string, expectedRestockingDate: string|null, isProductAvailableOnDomain: bool}>
     */
    public static function getIneffectiveExpectedRestockingDateData(): iterable
    {
        yield 'product is in stock even with a future date' => [
            'now' => '2026-07-12 12:00:00',
            'expectedRestockingDate' => '2026-07-22 00:00:00',
            'isProductAvailableOnDomain' => true,
        ];

        yield 'date is not filled' => [
            'now' => '2026-07-12 12:00:00',
            'expectedRestockingDate' => null,
            'isProductAvailableOnDomain' => false,
        ];

        yield 'date has passed' => [
            'now' => '2026-07-12 12:00:00',
            'expectedRestockingDate' => '2026-07-10 00:00:00',
            'isProductAvailableOnDomain' => false,
        ];

        yield 'date has passed at midnight' => [
            'now' => '2026-07-13 00:30:00',
            'expectedRestockingDate' => '2026-07-12 00:00:00',
            'isProductAvailableOnDomain' => false,
        ];
    }

    #[DataProvider('getIneffectiveExpectedRestockingDateData')]
    public function testEffectiveExpectedRestockingDateIsNull(
        string $now,
        ?string $expectedRestockingDate,
        bool $isProductAvailableOnDomain,
    ): void {
        $restockingDate = $expectedRestockingDate === null ? null : $this->createDate($expectedRestockingDate);
        $productAvailabilityFacade = $this->createProductAvailabilityFacade($isProductAvailableOnDomain, $now);
        $product = $this->createProduct($restockingDate);

        $effectiveDate = $productAvailabilityFacade->findEffectiveExpectedRestockingDate($product, Domain::FIRST_DOMAIN_ID);

        $this->assertNull($effectiveDate);
    }

    public function testDateValidityIsEvaluatedInDisplayTimezone(): void
    {
        // 22:30 UTC on the restocking day, but already 00:30 the next day in the Europe/Prague display timezone
        $productAvailabilityFacade = $this->createProductAvailabilityFacade(
            false,
            '2026-07-12 22:30:00',
            'Europe/Prague',
        );
        $product = $this->createProduct($this->createDate('2026-07-12 00:00:00'));

        $effectiveDate = $productAvailabilityFacade->findEffectiveExpectedRestockingDate($product, Domain::FIRST_DOMAIN_ID);

        $this->assertNull($effectiveDate);
    }

    public function testValidExpectedRestockingDateIsReturnedEvenWhenProductIsInStock(): void
    {
        $restockingDate = $this->createDate('2026-07-22 00:00:00');
        $productAvailabilityFacade = $this->createProductAvailabilityFacade(true, '2026-07-12 12:00:00');
        $product = $this->createProduct($restockingDate);

        $validDate = $productAvailabilityFacade->findValidExpectedRestockingDate($product, Domain::FIRST_DOMAIN_ID);

        $this->assertSame($restockingDate, $validDate);
    }

    public function testValidExpectedRestockingDateIsNullWhenDateHasPassed(): void
    {
        $productAvailabilityFacade = $this->createProductAvailabilityFacade(true, '2026-07-12 12:00:00');
        $product = $this->createProduct($this->createDate('2026-07-10 00:00:00'));

        $validDate = $productAvailabilityFacade->findValidExpectedRestockingDate($product, Domain::FIRST_DOMAIN_ID);

        $this->assertNull($validDate);
    }

    public function testAvailabilityStatusIsExpectedRestockWhenOutOfStockWithValidDate(): void
    {
        $productAvailabilityFacade = $this->createProductAvailabilityFacade(false, '2026-07-12 12:00:00');
        $product = $this->createProduct($this->createDate('2026-07-22 00:00:00'));

        $status = $productAvailabilityFacade->getProductAvailabilityStatusByDomainId($product, Domain::FIRST_DOMAIN_ID);

        $this->assertSame(AvailabilityStatusEnum::EXPECTED_RESTOCK, $status);
    }

    public function testAvailabilityStatusStaysOutOfStockWhenDateHasPassed(): void
    {
        $productAvailabilityFacade = $this->createProductAvailabilityFacade(false, '2026-07-12 12:00:00');
        $product = $this->createProduct($this->createDate('2026-07-10 00:00:00'));

        $status = $productAvailabilityFacade->getProductAvailabilityStatusByDomainId($product, Domain::FIRST_DOMAIN_ID);

        $this->assertSame(AvailabilityStatusEnum::OUT_OF_STOCK, $status);
    }

    public function testAvailabilityStatusStaysInStockRegardlessOfDate(): void
    {
        $productAvailabilityFacade = $this->createProductAvailabilityFacade(true, '2026-07-12 12:00:00');
        $product = $this->createProduct($this->createDate('2026-07-22 00:00:00'));

        $status = $productAvailabilityFacade->getProductAvailabilityStatusByDomainId($product, Domain::FIRST_DOMAIN_ID);

        $this->assertSame(AvailabilityStatusEnum::IN_STOCK, $status);
    }

    public function testAvailabilityInformationUsesExpectedRestockTextWhenDateIsEffective(): void
    {
        $productAvailabilityFacade = $this->createProductAvailabilityFacade(false, '2026-07-12 12:00:00');
        $product = $this->createProduct($this->createDate('2026-07-22 00:00:00'));

        $availabilityInformation = $productAvailabilityFacade->getProductAvailabilityInformationByDomainId(
            $product,
            Domain::FIRST_DOMAIN_ID,
        );

        $this->assertSame('Expecting ' . self::FORMATTED_DATE, $availabilityInformation);
    }

    public function testAvailabilityInformationStaysOutOfStockWhenDateHasPassed(): void
    {
        $productAvailabilityFacade = $this->createProductAvailabilityFacade(false, '2026-07-12 12:00:00');
        $product = $this->createProduct($this->createDate('2026-07-10 00:00:00'));

        $availabilityInformation = $productAvailabilityFacade->getProductAvailabilityInformationByDomainId(
            $product,
            Domain::FIRST_DOMAIN_ID,
        );

        $this->assertSame('Out of stock', $availabilityInformation);
    }

    private function createDate(string $dateTime): DateTimeImmutable
    {
        return new DateTimeImmutable($dateTime, new DateTimeZone('UTC'));
    }

    private function createProductAvailabilityFacade(
        bool $isProductAvailableOnDomain,
        string $now,
        string $displayTimezone = 'UTC',
    ): ProductAvailabilityFacade {
        $settingStub = $this->createStub(Setting::class);

        $productStockFacadeStub = $this->createStub(ProductStockFacade::class);
        $productStockFacadeStub->method('isProductAvailableOnDomain')->willReturn($isProductAvailableOnDomain);

        $clockStub = $this->createStub(ClockInterface::class);
        $clockStub->method('now')->willReturn($this->createDate($now));

        $dateTimeFormatterStub = $this->createStub(DateTimeFormatterInterface::class);
        $dateTimeFormatterStub->method('format')->willReturn(self::FORMATTED_DATE);

        $displayTimeZoneProviderStub = $this->createStub(DisplayTimeZoneProviderInterface::class);
        $displayTimeZoneProviderStub->method('getDisplayTimeZoneByDomainId')
            ->willReturn(new DateTimeZone($displayTimezone));

        return new ProductAvailabilityFacade(
            $settingStub,
            $productStockFacadeStub,
            $this->createStub(StoreFacade::class),
            $this->createDomain(),
            new InMemoryCache(),
            $clockStub,
            $dateTimeFormatterStub,
            $displayTimeZoneProviderStub,
        );
    }

    private function createProduct(?DateTimeImmutable $expectedRestockingDate): Product
    {
        $productStub = $this->createStub(Product::class);
        $productStub->method('getId')->willReturn(1);
        $productStub->method('getExpectedRestockingDate')->willReturn($expectedRestockingDate);

        return $productStub;
    }

    private function createDomain(): Domain
    {
        return new Domain(
            [DomainConfigHelper::getDomainConfig()],
            $this->createStub(Setting::class),
            $this->createStub(CurrentAdministrator::class),
        );
    }
}
