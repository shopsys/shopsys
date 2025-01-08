<?php

declare(strict_types=1);

namespace Tests\ProductFeed\MergadoBundle\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItem;
use Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItemFactory;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class MergadoFeedItemTest extends TestCase
{
    private const int MOCKED_SETTING_FEED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS = 8;

    private LoggerInterface|MockObject $loggerMock;

    private Setting|MockObject $settingMock;

    private PricingGroupSettingFacade|MockObject $pricingGroupSettingFacadeMock;

    private ProductPriceCalculation|MockObject $productPriceCalculationMock;

    private ImageFacade|MockObject $imageFacadeMock;

    private CategoryFacade|MockObject $categoryFacadeMock;

    private ProductParametersBatchLoader|MockObject $productParametersBatchLoaderMock;

    private ProductPriceCalculationForCustomerUser|MockObject $productPriceCalculationForCustomerUserMock;

    private CurrencyFacade|MockObject $currencyFacadeMock;

    private ProductUrlsBatchLoader|MockObject $productUrlsBatchLoaderMock;

    private ProductAvailabilityFacade|MockObject $productAvailabilityFacadeMock;

    private MergadoFeedItemFactory $mergadoFeedItemFactory;

    private Currency $defaultCurrency;

    private DomainConfig $defaultDomain;

    private Product|MockObject $defaultProduct;

    /**
     * @param bool $productIsAvailableOnStock
     * @param string $expectedAvailability
     * @param int $expectedDeliveryDays
     */
    #[DataProvider('mergadoFeedItemDataProvider')]
    public function testMergadoFeedItem(
        bool $productIsAvailableOnStock,
        string $expectedAvailability,
        int $expectedDeliveryDays,
    ): void {
        $this->doSetUp($productIsAvailableOnStock);

        $mergadoFeedItem = $this->mergadoFeedItemFactory->createForProduct($this->defaultProduct, $this->defaultDomain);

        $this->assertCommonFields($mergadoFeedItem);

        self::assertSame($expectedDeliveryDays, $mergadoFeedItem->getDeliveryDays());
        self::assertSame($expectedAvailability, $mergadoFeedItem->getAvailability());
    }

    /**
     * @return iterable
     */
    public static function mergadoFeedItemDataProvider(): iterable
    {
        yield 'product is available on stock' => [
            'productIsAvailableOnStock' => true,
            'expectedAvailability' => 'in stock',
            'expectedDeliveryDays' => 0,
        ];

        yield 'product is not available on stock' => [
            'productIsAvailableOnStock' => false,
            'expectedAvailability' => 'out of stock',
            'expectedDeliveryDays' => self::MOCKED_SETTING_FEED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS,
        ];
    }

    /**
     * @param \Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItem $mergadoFeedItem
     */
    private function assertCommonFields(MergadoFeedItem $mergadoFeedItem): void
    {
        self::assertSame(1, $mergadoFeedItem->getId());
        self::assertSame(1, $mergadoFeedItem->getSeekId());
        self::assertSame('product name', $mergadoFeedItem->getName());
        self::assertSame('catnum', $mergadoFeedItem->getProductNo());
        self::assertSame('https://example.com/product-1', $mergadoFeedItem->getUrl());
        self::assertSame('category1 > category2', $mergadoFeedItem->getCategoryPath());
        self::assertSame('short description usp 1', $mergadoFeedItem->getShortDescription());
        self::assertThat($mergadoFeedItem->getPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::zero()));
        self::assertThat($mergadoFeedItem->getPrice()->getPriceWithVat(), new IsMoneyEqual(Money::zero()));
        self::assertSame('https://example.com/img/product/1', $mergadoFeedItem->getImageUrl());
        self::assertSame('EUR', $mergadoFeedItem->getCurrencyCode());
        self::assertSame('description', $mergadoFeedItem->getDescription());
        self::assertThat($mergadoFeedItem->getHighProductPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::zero()));
        self::assertThat($mergadoFeedItem->getHighProductPrice()->getPriceWithVat(), new IsMoneyEqual(Money::zero()));
        self::assertNull($mergadoFeedItem->getBrand());
        self::assertSame([], $mergadoFeedItem->getFlags());
        self::assertSame([], $mergadoFeedItem->getParameters());
        self::assertSame([], $mergadoFeedItem->getGalleryImageUrls());
        self::assertNull($mergadoFeedItem->getMainVariantId());
    }

    /**
     * @param bool $isProductAvailableOnStock
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function doSetUp(bool $isProductAvailableOnStock): void
    {
        $this->productPriceCalculationForCustomerUserMock = $this->createMock(ProductPriceCalculationForCustomerUser::class);
        $this->productParametersBatchLoaderMock = $this->createMock(ProductParametersBatchLoader::class);
        $this->categoryFacadeMock = $this->createMock(CategoryFacade::class);
        $this->categoryFacadeMock->method('getCategoryNamesInPathFromRootToProductMainCategoryOnDomain')->willReturn(['category1', 'category2']);
        $this->currencyFacadeMock = $this->createMock(CurrencyFacade::class);
        $this->imageFacadeMock = $this->createMock(ImageFacade::class);
        $this->productPriceCalculationMock = $this->createMock(ProductPriceCalculation::class);
        $this->productUrlsBatchLoaderMock = $this->createMock(ProductUrlsBatchLoader::class);
        $this->pricingGroupSettingFacadeMock = $this->createMock(PricingGroupSettingFacade::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->settingMock = $this->createMock(Setting::class);

        $this->productAvailabilityFacadeMock = $this->createMock(ProductAvailabilityFacade::class);
        $this->productAvailabilityFacadeMock->method('isProductAvailableOnDomainCached')->willReturn($isProductAvailableOnStock);
        $this->productAvailabilityFacadeMock->method('getProductAvailabilityDaysForFeedsByDomainId')->willReturn($isProductAvailableOnStock ? 0 : self::MOCKED_SETTING_FEED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS);

        $this->mergadoFeedItemFactory = new MergadoFeedItemFactory(
            $this->productUrlsBatchLoaderMock,
            $this->productParametersBatchLoaderMock,
            $this->categoryFacadeMock,
            $this->productAvailabilityFacadeMock,
            $this->productPriceCalculationForCustomerUserMock,
            $this->imageFacadeMock,
            $this->currencyFacadeMock,
            $this->productPriceCalculationMock,
            $this->pricingGroupSettingFacadeMock,
            $this->loggerMock,
            $this->settingMock,
            new ImageUrlWithSizeHelper(),
        );

        $this->defaultCurrency = $this->createCurrencyMock(1, 'EUR');
        $this->defaultDomain = $this->createDomainConfigMock(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            'en',
            $this->defaultCurrency,
        );

        $this->defaultProduct = $this->createMock(Product::class);
        $this->defaultProduct->method('getId')->willReturn(1);
        $this->defaultProduct->method('getCatnum')->willReturn('catnum');
        $this->defaultProduct->method('getFullName')->willReturn('product name');
        $this->defaultProduct->method('getDescription')->willReturn('description');
        $this->defaultProduct->method('getShortDescriptionUsp1')->willReturn('short description usp 1');

        $this->mockProductPrice($this->defaultProduct, $this->defaultDomain, Price::zero());
        $this->mockProductUrl($this->defaultProduct, $this->defaultDomain, 'https://example.com/product-1');
        $this->mockProductImageUrl($this->defaultProduct, $this->defaultDomain, 'https://example.com/img/product/1');
    }

    /**
     * @param int $id
     * @param string $code
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    private function createCurrencyMock(int $id, string $code): Currency
    {
        $currencyMock = $this->createMock(Currency::class);

        $currencyMock->method('getId')->willReturn($id);
        $currencyMock->method('getCode')->willReturn($code);

        return $currencyMock;
    }

    /**
     * @param int $id
     * @param string $url
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private function createDomainConfigMock(int $id, string $url, string $locale, Currency $currency): DomainConfig
    {
        $domainConfigMock = $this->createMock(DomainConfig::class);

        $domainConfigMock->method('getId')->willReturn($id);
        $domainConfigMock->method('getUrl')->willReturn($url);
        $domainConfigMock->method('getLocale')->willReturn($locale);

        $this->currencyFacadeMock->method('getDomainDefaultCurrencyByDomainId')
            ->with($id)->willReturn($currency);

        return $domainConfigMock;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     */
    private function mockProductPrice(Product $product, DomainConfig $domain, Price $price): void
    {
        $domainId = $domain->getId();
        $pricingGroup = new PricingGroup(new PricingGroupData(), $domainId);
        $this->pricingGroupSettingFacadeMock->method('getDefaultPricingGroupByDomainId')->willReturn($pricingGroup);
        $productPrice = new ProductPrice($price, $pricingGroup, false);
        $this->productPriceCalculationForCustomerUserMock->method('calculatePriceForCustomerUserAndDomainId')
            ->with($product, $domainId, null)->willReturn($productPrice);

        $this->productPriceCalculationMock->method('calculatePrice')
            ->with($product, $domainId)->willReturn($productPrice);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domain
     * @param string $url
     */
    private function mockProductUrl(Product $product, DomainConfig $domain, string $url): void
    {
        $this->productUrlsBatchLoaderMock->method('getProductUrl')
            ->with($product, $domain)->willReturn($url);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domain
     * @param string $url
     */
    private function mockProductImageUrl(Product $product, DomainConfig $domain, string $url): void
    {
        $this->productUrlsBatchLoaderMock->method('getResizedProductImageUrl')
            ->with($product, $domain)->willReturn($url);
    }
}
