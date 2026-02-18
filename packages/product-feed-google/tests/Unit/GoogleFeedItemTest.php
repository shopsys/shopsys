<?php

declare(strict_types=1);

namespace Tests\ProductFeed\GoogleBundle\Unit;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
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
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\GoogleBundle\Model\FeedItem\GoogleFeedItemFactory;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class GoogleFeedItemTest extends TestCase
{
    private CurrencyFacade|MockObject $currencyFacadeMock;

    private ProductUrlsBatchLoader|MockObject $productUrlsBatchLoaderMock;

    private GoogleFeedItemFactory $googleFeedItemFactory;

    private DomainConfig $defaultDomain;

    private Product|MockObject $defaultProduct;

    #[Override]
    protected function setUp(): void
    {
        $this->doSetUp(true);
    }

    private function doSetUp(bool $isProductAvailableOnStock): void
    {
        $productPriceCalculation = $this->createProductPriceCalculationStub(Price::zero());

        $this->currencyFacadeMock = $this->createMock(CurrencyFacade::class);
        $this->productUrlsBatchLoaderMock = $this->createMock(ProductUrlsBatchLoader::class);
        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('isProductAvailableOnDomainCached')->willReturn($isProductAvailableOnStock);
        $specialPriceFacade = $this->createStub(SpecialPriceFacade::class);
        $pricingGroupSettingFacadeStub = $this->createStub(PricingGroupSettingFacade::class);
        $pricingGroupSettingFacadeStub->method('getDefaultPricingGroupByDomainId')->willReturn($this->createStub(PricingGroup::class));

        $this->googleFeedItemFactory = new GoogleFeedItemFactory(
            $productPriceCalculation,
            $this->currencyFacadeMock,
            $this->productUrlsBatchLoaderMock,
            $productAvailabilityFacadeStub,
            $specialPriceFacade,
            $pricingGroupSettingFacadeStub,
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

    public function testGoogleFeedItemOutOfStock(): void
    {
        $this->doSetUp(false);

        $googleFeedItem = $this->googleFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('out_of_stock', $googleFeedItem->getAvailability());
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
