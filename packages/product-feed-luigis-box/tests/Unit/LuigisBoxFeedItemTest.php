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
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
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

    private CurrentCurrencyProvider|MockObject $currentCurrencyProviderMock;

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

        $this->currentCurrencyProviderMock->expects($this->any())->method('getCurrentCurrencyOfDomain')
            ->with($id)->willReturn($currency);

        return $domainConfigStub;
    }

    private function mockProductPrice(Product $product, DomainConfig $domain, Price $price): void
    {
        $productPrice = new ProductPrice($price, $this->createStub(PricingGroup::class), false);
        $productPricesResult = new ProductPricesResult($productPrice, $productPrice);
        $this->productPriceCalculationForCustomerUserMock->expects($this->any())->method('calculatePricesForCustomerUserAndDomainId')
            ->with($product, $domain->getId(), null)->willReturn($productPricesResult);
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
        $brand = $this->createStub(Brand::class);
        $brand->method('getName')->willReturn(self::BRAND_NAME);
        $this->defaultProduct->expects($this->any())->method('getBrand')->willReturn($brand);

        $luigisBoxProductFeedItem = $this->luigisBoxProductFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::BRAND_NAME, $luigisBoxProductFeedItem->getBrand());
    }

    public function testLuigisBoxFeedItemWithDescription(): void
    {
        $this->defaultProduct->expects($this->any())->method('getDescriptionAsPlainText')
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

    private function doSetUp(bool $isProductAvailableOnStock): void
    {
        $this->currentCurrencyProviderMock = $this->createMock(CurrentCurrencyProvider::class);
        $this->productPriceCalculationForCustomerUserMock = $this->createMock(
            ProductPriceCalculationForCustomerUser::class,
        );
        $this->productUrlsBatchLoaderMock = $this->createMock(ProductUrlsBatchLoader::class);
        $this->defaultCurrency = $this->createCurrencyStub(1, self::EUR);
        $this->defaultDomain = $this->createDomainConfigStub(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            self::DEFAULT_LOCALE,
            $this->defaultCurrency,
        );

        $translator = $this->createStub(Translator::class);
        $translator->method('staticTrans')->willReturnArgument(0);
        Translator::injectSelf($translator);

        $flag = $this->createStub(Flag::class);
        $flag->method('getName')->willReturn(self::FLAG_NAME);
        $flag->method('isVisible')->willReturn('true');

        $mainCategory = $this->createMock(Category::class);
        $mainCategory->expects($this->any())->method('getId')->willReturn(self::MAIN_CATEGORY_ID);
        $mainCategory->expects($this->any())->method('getName')->with(self::DEFAULT_LOCALE)->willReturn(self::MAIN_CATEGORY_NAME);

        $this->defaultProduct = $this->createMock(Product::class);
        $this->defaultProduct->expects($this->any())->method('getId')->willReturn(self::PRODUCT_ID);
        $this->defaultProduct->expects($this->any())->method('getFullName')->with(self::DEFAULT_LOCALE)->willReturn(self::PRODUCT_NAME);
        $this->defaultProduct->expects($this->any())->method('getFlags')->willReturn([$flag]);
        $this->defaultProduct->expects($this->any())->method('getCategoriesIndexedByDomainId')->willReturn([self::MAIN_CATEGORY_ID => [$mainCategory]]);
        $this->defaultProduct->expects($this->any())->method('isMainVariant')->willReturn(false);
        $this->defaultProduct->expects($this->any())->method('isVariant')->willReturn(false);
        $this->defaultProduct->expects($this->any())->method('getEan')->willReturn(self::PRODUCT_EAN);
        $this->defaultProduct->expects($this->any())->method('getPartNo')->willReturn(self::PRODUCT_PART_NO);
        $this->defaultProduct->expects($this->any())->method('getCatnum')->willReturn(self::PRODUCT_SKU);

        $this->mockProductPrice($this->defaultProduct, $this->defaultDomain, Price::zero());
        $this->mockProductUrl($this->defaultProduct, $this->defaultDomain, self::PRODUCT_URL);

        $categoryRepositoryStub = $this->createStub(CategoryRepository::class);
        $categoryRepositoryStub->method('getProductMainCategoryOnDomain')->willReturn($mainCategory);

        $parameter = $this->createStub(Parameter::class);
        $parameter->method('getName')->willReturn(self::PARAMETER_NAME);

        $parameterValue = $this->createStub(ParameterValue::class);
        $parameterValue->method('getLocale')->willReturn(self::DEFAULT_LOCALE);
        $parameterValue->method('getText')->willReturn(self::PARAMETER_VALUE);

        $productParameterValue = new ProductParameterValue($this->defaultProduct, $parameter, $parameterValue);

        $productCachedAttributesFacade = $this->createStub(ProductCachedAttributesFacade::class);
        $productCachedAttributesFacade->method('getProductParameterValues')->willReturn([$productParameterValue]);

        $productAvailabilityFacade = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacade->method('isProductAvailableOnDomainCached')->willReturn($isProductAvailableOnStock);

        $settingStub = $this->createStub(Setting::class);
        $settingStub->method('getForDomain')->willReturn(self::MOCKED_LUIGIS_BOX_RANK_SETTING);

        $pricingSettingStub = $this->createStub(PricingSetting::class);
        $pricingSettingStub->method('getSellingPriceType')->willReturn(PricingSetting::PRICE_TYPE_WITH_VAT);

        $this->luigisBoxProductFeedItemFactory = new LuigisBoxProductFeedItemFactory(
            $this->productPriceCalculationForCustomerUserMock,
            $this->currentCurrencyProviderMock,
            $this->productUrlsBatchLoaderMock,
            $categoryRepositoryStub,
            $productCachedAttributesFacade,
            $productAvailabilityFacade,
            $settingStub,
            new ImageUrlWithSizeHelper(),
            $pricingSettingStub,
        );
    }

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
