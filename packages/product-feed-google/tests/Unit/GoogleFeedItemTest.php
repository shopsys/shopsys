<?php

declare(strict_types=1);

namespace Tests\ProductFeed\GoogleBundle\Unit;

use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductAdditionalServicesBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItem;
use Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItemFactory;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class GoogleFeedItemTest extends TestCase
{
    private CurrencyFacade|MockObject $currencyFacadeMock;

    private ProductUrlsBatchLoader|MockObject $productUrlsBatchLoaderMock;

    private Stub|ProductAdditionalServicesBatchLoader $productAdditionalServicesBatchLoaderStub;

    private GoogleFeedItemFactory $googleFeedItemFactory;

    private DomainConfig $defaultDomain;

    private Product|MockObject $defaultProduct;

    #[Override]
    protected function setUp(): void
    {
        $this->doSetUp(true);
    }

    private function doSetUp(
        bool $isProductAvailableOnStock,
        ?DateTimeImmutable $effectiveExpectedRestockingDate = null,
    ): void {
        $productPriceCalculation = $this->createProductPriceCalculationStub(Price::zero());

        $this->currencyFacadeMock = $this->createMock(CurrencyFacade::class);
        $this->productUrlsBatchLoaderMock = $this->createMock(ProductUrlsBatchLoader::class);
        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('isProductAvailableOnDomainCached')->willReturn($isProductAvailableOnStock);
        $productAvailabilityFacadeStub->method('findEffectiveExpectedRestockingDate')->willReturn($effectiveExpectedRestockingDate);
        $specialPriceFacade = $this->createStub(SpecialPriceFacade::class);
        $pricingGroupSettingFacadeStub = $this->createStub(PricingGroupSettingFacade::class);
        $pricingGroupSettingFacadeStub->method('getDefaultPricingGroupByDomainId')->willReturn($this->createStub(PricingGroup::class));

        $this->productAdditionalServicesBatchLoaderStub = $this->createStub(ProductAdditionalServicesBatchLoader::class);

        $this->googleFeedItemFactory = new GoogleFeedItemFactory(
            $productPriceCalculation,
            $this->currencyFacadeMock,
            $this->productUrlsBatchLoaderMock,
            $productAvailabilityFacadeStub,
            $specialPriceFacade,
            $pricingGroupSettingFacadeStub,
            $this->productAdditionalServicesBatchLoaderStub,
        );

        $defaultCurrency = $this->createCurrencyStub(1, 'EUR');
        $this->defaultDomain = $this->createDomainConfigStub(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            'en',
            $defaultCurrency,
        );

        $this->defaultProduct = $this->createMock(Product::class);
        $this->defaultProduct->expects($this->any())->method('getId')->willReturn(1);
        $this->defaultProduct->expects($this->any())->method('getFullName')->with('en')->willReturn('product name');

        $this->mockProductUrl($this->defaultProduct, $this->defaultDomain, 'https://example.com/product-1');
    }

    private function createCurrencyStub(int $id, string $code): Currency
    {
        $currencyStub = $this->createStub(Currency::class);

        $currencyStub->method('getId')->willReturn($id);
        $currencyStub->method('getCode')->willReturn($code);

        return $currencyStub;
    }

    private function createDomainConfigStub(int $id, string $url, string $locale, Currency $currency): DomainConfig
    {
        $domainConfigStub = $this->createStub(DomainConfig::class);

        $domainConfigStub->method('getId')->willReturn($id);
        $domainConfigStub->method('getUrl')->willReturn($url);
        $domainConfigStub->method('getLocale')->willReturn($locale);

        $this->currencyFacadeMock->expects($this->any())->method('getDomainDefaultCurrencyByDomainId')
            ->with($id)->willReturn($currency);

        return $domainConfigStub;
    }

    private function createProductPriceCalculationStub(Price $price): ProductPriceCalculation
    {
        $productPrice = new ProductPrice($price, $this->createStub(PricingGroup::class), false);

        $productPriceCalculationStub = $this->createStub(ProductPriceCalculation::class);

        $productPriceCalculationStub->method('calculatePrice')->willReturn($productPrice);

        return $productPriceCalculationStub;
    }

    private function mockProductUrl(Product $product, DomainConfig $domain, string $url): void
    {
        $this->productUrlsBatchLoaderMock->expects($this->any())->method('getProductUrl')
            ->with($product, $domain)->willReturn($url);
    }

    private function mockProductImageUrl(Product $product, DomainConfig $domain, string $url): void
    {
        $this->productUrlsBatchLoaderMock->expects($this->any())->method('getProductImageUrl')
            ->with($product, $domain)->willReturn($url);
    }

    public function testGoogleFeedItemWithCustomLabel0(): void
    {
        $this->productAdditionalServicesBatchLoaderStub->method('getShownInFeedsFeedNames')
            ->willReturn(['Assembly', 'Extended warranty']);

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertSame('Assembly;Extended warranty', $googleFeedItem->getCustomLabel0());
    }

    public function testGoogleFeedItemCustomLabel0SanitizesSeparatorInFeedNames(): void
    {
        $this->productAdditionalServicesBatchLoaderStub->method('getShownInFeedsFeedNames')
            ->willReturn(['Assembly; anchoring', 'Extended warranty']);

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertSame('Assembly, anchoring;Extended warranty', $googleFeedItem->getCustomLabel0());
    }

    public function testGoogleFeedItemCustomLabel0IsLimitedTo100Characters(): void
    {
        $this->productAdditionalServicesBatchLoaderStub->method('getShownInFeedsFeedNames')
            ->willReturn([str_repeat('a', 60), str_repeat('b', 39), str_repeat('c', 60)]);

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertSame(str_repeat('a', 60) . ';' . str_repeat('b', 39), $googleFeedItem->getCustomLabel0());
        self::assertSame(100, mb_strlen($googleFeedItem->getCustomLabel0()));
    }

    public function testGoogleFeedItemCustomLabel0LeavesOutOverlongFeedNameInsteadOfCuttingIt(): void
    {
        $this->productAdditionalServicesBatchLoaderStub->method('getShownInFeedsFeedNames')
            ->willReturn([str_repeat('a', 101), 'Assembly']);

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertSame('Assembly', $googleFeedItem->getCustomLabel0());
    }

    public function testGoogleFeedItemCustomLabel0LeavesOutFeedNameNotFittingWithItsSeparator(): void
    {
        $this->productAdditionalServicesBatchLoaderStub->method('getShownInFeedsFeedNames')
            ->willReturn([str_repeat('a', 99), str_repeat('b', 5)]);

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertSame(str_repeat('a', 99), $googleFeedItem->getCustomLabel0());
    }

    public function testGoogleFeedItemCustomLabel0IsNullWhenNoFeedNameFits(): void
    {
        $this->productAdditionalServicesBatchLoaderStub->method('getShownInFeedsFeedNames')
            ->willReturn([str_repeat('a', 101)]);

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertNull($googleFeedItem->getCustomLabel0());
    }

    public function testMinimalGoogleFeedItemIsCreatable(): void
    {
        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(1, $googleFeedItem->getId());
        self::assertEquals(1, $googleFeedItem->getSeekId());
        self::assertEquals('product name', $googleFeedItem->getTitle());
        self::assertNull($googleFeedItem->getBrand());
        self::assertNull($googleFeedItem->getDescription());
        self::assertEquals('https://example.com/product-1', $googleFeedItem->getLink());
        self::assertNull($googleFeedItem->getImageLink());
        self::assertEquals('in_stock', $googleFeedItem->getAvailability());
        self::assertThat($googleFeedItem->getPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::zero()));
        self::assertThat($googleFeedItem->getPrice()->getPriceWithVat(), new IsMoneyEqual(Money::zero()));
        self::assertEquals('EUR', $googleFeedItem->getCurrency()->getCode());
        self::assertEquals([], $googleFeedItem->getIdentifiers());
        self::assertNull($googleFeedItem->getCustomLabel0());
    }

    public function testGoogleFeedItemWithBrand(): void
    {
        $brand = $this->createStub(Brand::class);
        $brand->method('getName')->willReturn('brand name');
        $this->defaultProduct->expects($this->any())->method('getBrand')->willReturn($brand);

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('brand name', $googleFeedItem->getBrand());
    }

    public function testGoogleFeedItemWithDescription(): void
    {
        $this->defaultProduct->expects($this->any())->method('getDescriptionAsPlainText')
            ->with(1)->willReturn('product description');

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('product description', $googleFeedItem->getDescription());
    }

    public function testGoogleFeedItemWithImageLink(): void
    {
        $this->mockProductImageUrl($this->defaultProduct, $this->defaultDomain, 'https://example.com/img/product/1');

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('https://example.com/img/product/1', $googleFeedItem->getImageLink());
    }

    /**
     * @return iterable<string, array{isProductAvailableOnStock: bool, effectiveExpectedRestockingDate: \DateTimeImmutable|null, isAllowedNegativeStock: bool, expectedAvailability: string, expectedAvailabilityDate: \DateTimeImmutable|null}>
     */
    public static function getAvailabilityData(): iterable
    {
        $expectedRestockingDate = new DateTimeImmutable('midnight +14 days');

        yield 'product in stock' => [
            'isProductAvailableOnStock' => true,
            'effectiveExpectedRestockingDate' => null,
            'isAllowedNegativeStock' => true,
            'expectedAvailability' => GoogleFeedItem::AVAILABILITY_IN_STOCK,
            'expectedAvailabilityDate' => null,
        ];

        yield 'product out of stock' => [
            'isProductAvailableOnStock' => false,
            'effectiveExpectedRestockingDate' => null,
            'isAllowedNegativeStock' => false,
            'expectedAvailability' => GoogleFeedItem::AVAILABILITY_OUT_OF_STOCK,
            'expectedAvailabilityDate' => null,
        ];

        yield 'out of stock with restocking date and allowed negative stock is backorder' => [
            'isProductAvailableOnStock' => false,
            'effectiveExpectedRestockingDate' => $expectedRestockingDate,
            'isAllowedNegativeStock' => true,
            'expectedAvailability' => GoogleFeedItem::AVAILABILITY_BACKORDER,
            'expectedAvailabilityDate' => $expectedRestockingDate,
        ];

        yield 'out of stock with restocking date but denied negative stock stays out of stock' => [
            'isProductAvailableOnStock' => false,
            'effectiveExpectedRestockingDate' => $expectedRestockingDate,
            'isAllowedNegativeStock' => false,
            'expectedAvailability' => GoogleFeedItem::AVAILABILITY_OUT_OF_STOCK,
            'expectedAvailabilityDate' => null,
        ];

        yield 'out of stock with allowed negative stock but no date stays out of stock' => [
            'isProductAvailableOnStock' => false,
            'effectiveExpectedRestockingDate' => null,
            'isAllowedNegativeStock' => true,
            'expectedAvailability' => GoogleFeedItem::AVAILABILITY_OUT_OF_STOCK,
            'expectedAvailabilityDate' => null,
        ];
    }

    #[DataProvider('getAvailabilityData')]
    public function testGoogleFeedItemAvailability(
        bool $isProductAvailableOnStock,
        ?DateTimeImmutable $effectiveExpectedRestockingDate,
        bool $isAllowedNegativeStock,
        string $expectedAvailability,
        ?DateTimeImmutable $expectedAvailabilityDate,
    ): void {
        $this->doSetUp($isProductAvailableOnStock, $effectiveExpectedRestockingDate);
        $this->defaultProduct->method('isAllowedNegativeStock')->willReturn($isAllowedNegativeStock);

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertSame($expectedAvailability, $googleFeedItem->getAvailability());
        self::assertSame($expectedAvailabilityDate, $googleFeedItem->getAvailabilityDate());
    }

    public function testGoogleFeedItemWithEan(): void
    {
        $this->defaultProduct->expects($this->any())->method('getEan')->willReturn('1234567890123');

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(['gtin' => '1234567890123'], $googleFeedItem->getIdentifiers());
    }

    public function testGoogleFeedItemWithPartno(): void
    {
        $this->defaultProduct->expects($this->any())->method('getPartno')->willReturn('HSC0424PP');

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(['mpn' => 'HSC0424PP'], $googleFeedItem->getIdentifiers());
    }

    public function testGoogleFeedItemWithEanAndPartno(): void
    {
        $this->defaultProduct->expects($this->any())->method('getEan')->willReturn('1234567890123');
        $this->defaultProduct->expects($this->any())->method('getPartno')->willReturn('HSC0424PP');

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(['gtin' => '1234567890123', 'mpn' => 'HSC0424PP'], $googleFeedItem->getIdentifiers());
    }
}
