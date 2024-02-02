<?php

declare(strict_types=1);

namespace Tests\ProductFeed\LuigisBoxBundle\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxFeedItem;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxFeedItemFactory;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class LuigisBoxFeedItemTest extends TestCase
{
    private const MAIN_CATEGORY_ID = 1;
    private const MAIN_CATEGORY_NAME = 'Main category';
    private const FLAG_NAME = 'Flag name';
    private const PRODUCT_NAME = 'product name';
    private const PRODUCT_URL = 'https://example.com/product-1';
    private const PRODUCT_EAN = 'ean123';
    private const PRODUCT_PART_NO = 'partNo123';
    private const PRODUCT_SKU = 'sku123';
    private const PRODUCT_ID = 1;
    private const PRODUCT_DESCRIPTION = 'product description';
    private const EUR = 'EUR';
    private const IMAGE_URL = 'https://example.com/img/product/1';
    private const BRAND_NAME = 'brand name';
    private const PARAMETER_NAME = 'parameter';
    private const PARAMETER_VALUE = 'value';
    private const DEFAULT_LOCALE = 'en';

    private ProductPriceCalculationForCustomerUser|MockObject $productPriceCalculationForCustomerUserMock;

    private CurrencyFacade|MockObject $currencyFacadeMock;

    private ProductUrlsBatchLoader|MockObject $productUrlsBatchLoaderMock;

    private LuigisBoxFeedItemFactory $luigisBoxFeedItemFactory;

    private Currency $defaultCurrency;

    private DomainConfig $defaultDomain;

    private Product|MockObject $defaultProduct;

    protected function setUp(): void
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
        $this->defaultProduct->method('getName')->with(self::DEFAULT_LOCALE)->willReturn(self::PRODUCT_NAME);
        $this->defaultProduct->method('getCalculatedSellingDenied')->willReturn(false);
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
        $parameter->method('isVisible')->willReturn(true);

        $parameterValue = $this->createMock(ParameterValue::class);
        $parameterValue->method('getLocale')->willReturn(self::DEFAULT_LOCALE);
        $parameterValue->method('getText')->willReturn(self::PARAMETER_VALUE);

        $productParameterValue = new ProductParameterValue($this->defaultProduct, $parameter, $parameterValue);

        $productCachedAttributesFacade = $this->createMock(ProductCachedAttributesFacade::class);
        $productCachedAttributesFacade->method('getProductParameterValues')->willReturn([$productParameterValue]);

        $productAvailabilityFacade = $this->createMock(ProductAvailabilityFacade::class);

        $this->luigisBoxFeedItemFactory = new LuigisBoxFeedItemFactory(
            $this->productPriceCalculationForCustomerUserMock,
            $this->currencyFacadeMock,
            $this->productUrlsBatchLoaderMock,
            $categoryRepositoryMock,
            $productCachedAttributesFacade,
            $productAvailabilityFacade,
        );
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
        $productPrice = new ProductPrice($price, false);
        $this->productPriceCalculationForCustomerUserMock->method('calculatePriceForCustomerUserAndDomainId')
            ->with($product, $domain->getId(), null)->willReturn($productPrice);
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

    public function testMinimalLuigisBoxFeedItemIsCreatable(): void
    {
        $luigisBoxFeedItem = $this->luigisBoxFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertInstanceOf(LuigisBoxFeedItem::class, $luigisBoxFeedItem);
        self::assertEquals(self::PRODUCT_ID, $luigisBoxFeedItem->getId());
        self::assertEquals(self::PRODUCT_ID, $luigisBoxFeedItem->getSeekId());
        self::assertEquals(self::PRODUCT_NAME, $luigisBoxFeedItem->getTitle());
        self::assertEquals(self::PRODUCT_URL, $luigisBoxFeedItem->getLink());
        self::assertEquals(self::PRODUCT_EAN, $luigisBoxFeedItem->getEan());
        self::assertEquals(self::PRODUCT_PART_NO, $luigisBoxFeedItem->getPartNo());
        self::assertEquals(self::PRODUCT_SKU, $luigisBoxFeedItem->getSku());
        self::assertNull($luigisBoxFeedItem->getDescription());
        self::assertEquals(self::MAIN_CATEGORY_ID, $luigisBoxFeedItem->getCategoryId());
        self::assertEquals(self::MAIN_CATEGORY_NAME, $luigisBoxFeedItem->getCategoryText());
        self::assertEquals([0 => self::MAIN_CATEGORY_ID], $luigisBoxFeedItem->getHierarchyIdsIndexedByCategoryId());
        self::assertEquals([0 => self::MAIN_CATEGORY_NAME], $luigisBoxFeedItem->getHierarchyNamesIndexedByCategoryId());
        self::assertNull($luigisBoxFeedItem->getImageLink());
        self::assertEquals(t('In stock'), $luigisBoxFeedItem->getAvailability());
        self::assertThat($luigisBoxFeedItem->getPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::zero()));
        self::assertThat($luigisBoxFeedItem->getPrice()->getPriceWithVat(), new IsMoneyEqual(Money::zero()));
        self::assertEquals(self::EUR, $luigisBoxFeedItem->getCurrency()->getCode());
        self::assertNull($luigisBoxFeedItem->getBrand());
        self::assertEquals(['Flag_name'], $luigisBoxFeedItem->getFlagNames());
        self::assertEquals([self::PARAMETER_NAME => self::PARAMETER_VALUE], $luigisBoxFeedItem->getProductParameterValuesIndexedByName());
    }

    public function testLuigisBoxFeedItemWithBrand(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|\PHPUnit\Framework\MockObject\MockObject $brand */
        $brand = $this->createMock(Brand::class);
        $brand->method('getName')->willReturn(self::BRAND_NAME);
        $this->defaultProduct->method('getBrand')->willReturn($brand);

        $luigisBoxFeedItem = $this->luigisBoxFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::BRAND_NAME, $luigisBoxFeedItem->getBrand());
    }

    public function testLuigisBoxFeedItemWithDescription(): void
    {
        $this->defaultProduct->method('getDescriptionAsPlainText')
            ->with($this->defaultDomain->getId())->willReturn(self::PRODUCT_DESCRIPTION);

        $luigisBoxFeedItem = $this->luigisBoxFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::PRODUCT_DESCRIPTION, $luigisBoxFeedItem->getDescription());
    }

    public function testLuigisBoxFeedItemWithImageLink(): void
    {
        $this->mockProductImageUrl($this->defaultProduct, $this->defaultDomain, self::IMAGE_URL);

        $luigisBoxFeedItem = $this->luigisBoxFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::IMAGE_URL, $luigisBoxFeedItem->getImageLink());
    }

    public function testLuigisBoxFeedItemWithSellingDenied(): void
    {
        $product = clone $this->defaultProduct;
        $product->method('getCalculatedSellingDenied')->willReturn(true);

        $luigisBoxFeedItem = $this->luigisBoxFeedItemFactory->create($product, $this->defaultDomain);

        self::assertEquals(t('Out of stock'), $luigisBoxFeedItem->getAvailability());
    }
}
