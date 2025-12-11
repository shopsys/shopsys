<?php

declare(strict_types=1);

namespace Tests\ProductFeed\LuigisBoxBundle\Unit;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPricesResult;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItemFactory;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class LuigisBoxFeedItemTest extends TestCase
{
    private const int MOCKED_LUIGIS_BOX_RANK_SETTING = 8;
    private const int MAIN_CATEGORY_ID = 1;
    private const string MAIN_CATEGORY_NAME = 'Main category';
    private const string PRODUCT_IDENTITY = 'product-1';
    private const string FLAG_NAME = 'Flag name';
    private const string PRODUCT_NAME = 'product name';
    private const string PRODUCT_URL = 'https://example.com/product-1';
    private const string PRODUCT_EAN = 'ean123';
    private const string PRODUCT_PART_NO = 'partNo123';
    private const string PRODUCT_SKU = 'sku123';
    private const int PRODUCT_ID = 1;
    private const string PRODUCT_DESCRIPTION = 'product description';
    private const string EUR = 'EUR';
    private const string IMAGE_URL = 'https://example.com/img/product/1';
    private const string BRAND_NAME = 'brand name';
    private const string PARAMETER_NAME = 'parameter';
    private const string PARAMETER_VALUE = 'value';
    private const string DEFAULT_LOCALE = 'en';

    private ProductPriceCalculationForCustomerUser|MockObject $productPriceCalculationForCustomerUserMock;

    private CurrencyFacade|MockObject $currencyFacadeMock;

    private ProductUrlsBatchLoader|MockObject $productUrlsBatchLoaderMock;

    private LuigisBoxProductFeedItemFactory $luigisBoxProductFeedItemFactory;

    private Currency $defaultCurrency;

    private DomainConfig $defaultDomain;

    private Product|MockObject $defaultProduct;

    #[Override]
    protected function setUp(): void
    {
        $this->doSetUp(true);
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
        $productPrice = new ProductPrice($price, $this->createMock(PricingGroup::class), false);
        $productPricesResult = new ProductPricesResult($productPrice, $productPrice);
        $this->productPriceCalculationForCustomerUserMock->method('calculatePricesForCustomerUserAndDomainId')
            ->with($product, $domain->getId(), null)->willReturn($productPricesResult);
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
        $this->productUrlsBatchLoaderMock->method('getProductImageUrl')
            ->with($product, $domain)->willReturn($url);
    }

    /**
     * @param bool $productIsAvailableOnStock
     * @param int $expectedRank
     */
    #[DataProvider('luigisBoxFeedItemDataProvider')]
    public function testMinimalLuigisBoxFeedItemIsCreatable(bool $productIsAvailableOnStock, int $expectedRank): void
    {
        $this->doSetUp($productIsAvailableOnStock);
        $luigisBoxProductFeedItem = $this->luigisBoxProductFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        $this->assertCommonFields($luigisBoxProductFeedItem);

        self::assertSame(
            $productIsAvailableOnStock ? t('Out of stock', domain: Translator::CUSTOMER_TRANSLATION_DOMAIN) : t('In stock', domain: Translator::CUSTOMER_TRANSLATION_DOMAIN),
            $luigisBoxProductFeedItem->getAvailabilityRankText(),
        );
        self::assertSame($expectedRank, $luigisBoxProductFeedItem->getAvailabilityRank());
    }

    /**
     * @return iterable
     */
    public static function luigisBoxFeedItemDataProvider(): iterable
    {
        yield 'product is available on stock' => [
            'productIsAvailableOnStock' => true,
            'expectedRank' => 1,
        ];

        yield 'product is not available on stock' => [
            'productIsAvailableOnStock' => false,
            'expectedRank' => self::MOCKED_LUIGIS_BOX_RANK_SETTING,
        ];
    }

    public function testLuigisBoxFeedItemWithBrand(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|\PHPUnit\Framework\MockObject\MockObject $brand */
        $brand = $this->createMock(Brand::class);
        $brand->method('getName')->willReturn(self::BRAND_NAME);
        $this->defaultProduct->method('getBrand')->willReturn($brand);

        $luigisBoxProductFeedItem = $this->luigisBoxProductFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::BRAND_NAME, $luigisBoxProductFeedItem->getBrand());
    }

    public function testLuigisBoxFeedItemWithDescription(): void
    {
        $this->defaultProduct->method('getDescriptionAsPlainText')
            ->with($this->defaultDomain->getId())->willReturn(self::PRODUCT_DESCRIPTION);

        $luigisBoxProductFeedItem = $this->luigisBoxProductFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::PRODUCT_DESCRIPTION, $luigisBoxProductFeedItem->getDescription());
    }

    public function testLuigisBoxFeedItemWithImageLink(): void
    {
        $this->mockProductImageUrl($this->defaultProduct, $this->defaultDomain, self::IMAGE_URL);

        $luigisBoxProductFeedItem = $this->luigisBoxProductFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::IMAGE_URL . '?width=100&height=100', $luigisBoxProductFeedItem->getImageLinkS());
    }

    /**
     * @param bool $isProductAvailableOnStock
     */
    private function doSetUp(bool $isProductAvailableOnStock): void
    {
        $this->currencyFacadeMock = $this->createMock(CurrencyFacade::class);
        $this->productPriceCalculationForCustomerUserMock = $this->createMock(
            ProductPriceCalculationForCustomerUser::class,
        );
        $this->productUrlsBatchLoaderMock = $this->createMock(ProductUrlsBatchLoader::class);
        $this->defaultCurrency = $this->createCurrencyMock(1, self::EUR);
        $this->defaultDomain = $this->createDomainConfigMock(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            self::DEFAULT_LOCALE,
            $this->defaultCurrency,
        );

        $translator = $this->createMock(Translator::class);
        $translator->method('staticTrans')->willReturnArgument(0);
        Translator::injectSelf($translator);

        $flag = $this->createMock(Flag::class);
        $flag->method('getName')->willReturn(self::FLAG_NAME);
        $flag->method('isVisible')->willReturn('true');

        $mainCategory = $this->createMock(Category::class);
        $mainCategory->method('getId')->willReturn(self::MAIN_CATEGORY_ID);
        $mainCategory->method('getName')->with(self::DEFAULT_LOCALE)->willReturn(self::MAIN_CATEGORY_NAME);

        $this->defaultProduct = $this->createMock(Product::class);
        $this->defaultProduct->method('getId')->willReturn(self::PRODUCT_ID);
        $this->defaultProduct->method('getFullName')->with(self::DEFAULT_LOCALE)->willReturn(self::PRODUCT_NAME);
        $this->defaultProduct->method('getFlags')->willReturn([$flag]);
        $this->defaultProduct->method('getCategoriesIndexedByDomainId')->willReturn([self::MAIN_CATEGORY_ID => [$mainCategory]]);
        $this->defaultProduct->method('isMainVariant')->willReturn(false);
        $this->defaultProduct->method('isVariant')->willReturn(false);
        $this->defaultProduct->method('getEan')->willReturn(self::PRODUCT_EAN);
        $this->defaultProduct->method('getPartNo')->willReturn(self::PRODUCT_PART_NO);
        $this->defaultProduct->method('getCatnum')->willReturn(self::PRODUCT_SKU);

        $this->mockProductPrice($this->defaultProduct, $this->defaultDomain, Price::zero());
        $this->mockProductUrl($this->defaultProduct, $this->defaultDomain, self::PRODUCT_URL);

        $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $categoryRepositoryMock->method('getProductMainCategoryOnDomain')->willReturn($mainCategory);

        $parameter = $this->createMock(Parameter::class);
        $parameter->method('getName')->willReturn(self::PARAMETER_NAME);

        $parameterValue = $this->createMock(ParameterValue::class);
        $parameterValue->method('getLocale')->willReturn(self::DEFAULT_LOCALE);
        $parameterValue->method('getText')->willReturn(self::PARAMETER_VALUE);

        $productParameterValue = new ProductParameterValue($this->defaultProduct, $parameter, $parameterValue);

        $productCachedAttributesFacade = $this->createMock(ProductCachedAttributesFacade::class);
        $productCachedAttributesFacade->method('getProductParameterValues')->willReturn([$productParameterValue]);

        $productAvailabilityFacade = $this->createMock(ProductAvailabilityFacade::class);
        $productAvailabilityFacade->method('isProductAvailableOnDomainCached')->willReturn($isProductAvailableOnStock);

        $settingMock = $this->createMock(Setting::class);
        $settingMock->method('getForDomain')->willReturn(self::MOCKED_LUIGIS_BOX_RANK_SETTING);

        $pricingSettingMock = $this->createMock(PricingSetting::class);
        $pricingSettingMock->method('getSellingPriceType')->willReturn(PricingSetting::PRICE_TYPE_WITH_VAT);

        $this->luigisBoxProductFeedItemFactory = new LuigisBoxProductFeedItemFactory(
            $this->productPriceCalculationForCustomerUserMock,
            $this->currencyFacadeMock,
            $this->productUrlsBatchLoaderMock,
            $categoryRepositoryMock,
            $productCachedAttributesFacade,
            $productAvailabilityFacade,
            $settingMock,
            new ImageUrlWithSizeHelper(),
            $pricingSettingMock,
        );
    }

    /**
     * @param \Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem $luigisBoxProductFeedItem
     */
    private function assertCommonFields(LuigisBoxProductFeedItem $luigisBoxProductFeedItem): void
    {
        self::assertSame(self::PRODUCT_IDENTITY, $luigisBoxProductFeedItem->getIdentity());
        self::assertSame(self::PRODUCT_ID, $luigisBoxProductFeedItem->getSeekId());
        self::assertSame(self::PRODUCT_NAME, $luigisBoxProductFeedItem->getTitle());
        self::assertSame(self::PRODUCT_URL, $luigisBoxProductFeedItem->getUrl());
        self::assertSame(self::PRODUCT_EAN, $luigisBoxProductFeedItem->getEan());
        self::assertSame(self::PRODUCT_SKU, $luigisBoxProductFeedItem->getProductCode());
        self::assertNull($luigisBoxProductFeedItem->getDescription());
        self::assertSame([self::MAIN_CATEGORY_ID => self::MAIN_CATEGORY_NAME], $luigisBoxProductFeedItem->getCategoryNamesIndexedByCategoryId());
        self::assertNull($luigisBoxProductFeedItem->getImageLinkS());
        self::assertNull($luigisBoxProductFeedItem->getImageLinkM());
        self::assertNull($luigisBoxProductFeedItem->getImageLinkL());
        self::assertThat($luigisBoxProductFeedItem->getPrice(), new IsMoneyEqual(Money::zero()));
        self::assertSame(self::EUR, $luigisBoxProductFeedItem->getCurrency()->getCode());
        self::assertNull($luigisBoxProductFeedItem->getBrand());
        self::assertSame([self::FLAG_NAME], $luigisBoxProductFeedItem->getFlagNames());
        self::assertSame([self::PARAMETER_NAME => self::PARAMETER_VALUE], $luigisBoxProductFeedItem->getProductParameterValuesIndexedByName());
        self::assertSame(1, $luigisBoxProductFeedItem->getAvailability());
    }
}
