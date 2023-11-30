<?php

declare(strict_types=1);

namespace Tests\ProductFeed\PersooBundle\Unit;

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
use Shopsys\ProductFeed\PersooBundle\Model\FeedItem\PersooFeedItem;
use Shopsys\ProductFeed\PersooBundle\Model\FeedItem\PersooFeedItemFactory;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class PersooFeedItemTest extends TestCase
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

    private PersooFeedItemFactory $persooFeedItemFactory;

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

        $this->persooFeedItemFactory = new PersooFeedItemFactory(
            $this->productPriceCalculationForCustomerUserMock,
            $this->currencyFacadeMock,
            $this->productUrlsBatchLoaderMock,
            $categoryRepositoryMock,
            $productCachedAttributesFacade,
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

    public function testMinimalPersooFeedItemIsCreatable(): void
    {
        $persooFeedItem = $this->persooFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertInstanceOf(PersooFeedItem::class, $persooFeedItem);
        self::assertEquals(self::PRODUCT_ID, $persooFeedItem->getId());
        self::assertEquals(self::PRODUCT_ID, $persooFeedItem->getSeekId());
        self::assertEquals(self::PRODUCT_NAME, $persooFeedItem->getTitle());
        self::assertEquals(self::PRODUCT_URL, $persooFeedItem->getLink());
        self::assertEquals(self::PRODUCT_EAN, $persooFeedItem->getEan());
        self::assertEquals(self::PRODUCT_PART_NO, $persooFeedItem->getPartNo());
        self::assertEquals(self::PRODUCT_SKU, $persooFeedItem->getSku());
        self::assertNull($persooFeedItem->getDescription());
        self::assertEquals(self::MAIN_CATEGORY_ID, $persooFeedItem->getCategoryId());
        self::assertEquals(self::MAIN_CATEGORY_NAME, $persooFeedItem->getCategoryText());
        self::assertEquals([0 => self::MAIN_CATEGORY_ID], $persooFeedItem->getHierarchyIdsIndexedByCategoryId());
        self::assertEquals([0 => self::MAIN_CATEGORY_NAME], $persooFeedItem->getHierarchyNamesIndexedByCategoryId());
        self::assertNull($persooFeedItem->getImageLink());
        self::assertEquals(t('In stock'), $persooFeedItem->getAvailability());
        self::assertThat($persooFeedItem->getPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::zero()));
        self::assertThat($persooFeedItem->getPrice()->getPriceWithVat(), new IsMoneyEqual(Money::zero()));
        self::assertEquals(self::EUR, $persooFeedItem->getCurrency()->getCode());
        self::assertNull($persooFeedItem->getBrand());
        self::assertEquals(['Flag_name'], $persooFeedItem->getFlagNames());
        self::assertEquals([self::PARAMETER_NAME => self::PARAMETER_VALUE], $persooFeedItem->getProductParameterValuesIndexedByName());
    }

    public function testPersooFeedItemWithBrand(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|\PHPUnit\Framework\MockObject\MockObject $brand */
        $brand = $this->createMock(Brand::class);
        $brand->method('getName')->willReturn(self::BRAND_NAME);
        $this->defaultProduct->method('getBrand')->willReturn($brand);

        $persooFeedItem = $this->persooFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::BRAND_NAME, $persooFeedItem->getBrand());
    }

    public function testPersooFeedItemWithDescription(): void
    {
        $this->defaultProduct->method('getDescription')
            ->with($this->defaultDomain->getId())->willReturn(self::PRODUCT_DESCRIPTION);

        $persooFeedItem = $this->persooFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::PRODUCT_DESCRIPTION, $persooFeedItem->getDescription());
    }

    public function testPersooFeedItemWithImageLink(): void
    {
        $this->mockProductImageUrl($this->defaultProduct, $this->defaultDomain, self::IMAGE_URL);

        $persooFeedItem = $this->persooFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::IMAGE_URL, $persooFeedItem->getImageLink());
    }

    public function testPersooFeedItemWithSellingDenied(): void
    {
        $product = clone $this->defaultProduct;
        $product->method('getCalculatedSellingDenied')->willReturn(true);

        $persooFeedItem = $this->persooFeedItemFactory->create($product, $this->defaultDomain);

        self::assertEquals(t('Out of stock'), $persooFeedItem->getAvailability());
    }
}
