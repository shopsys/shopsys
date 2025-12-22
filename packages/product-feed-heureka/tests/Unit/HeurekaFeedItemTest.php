<?php

declare(strict_types=1);

namespace Tests\ProductFeed\HeurekaBundle\Unit;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPricesResult;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFactory;
use Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaProductDataBatchLoader;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Shopsys\ProductFeed\HeurekaBundle\Model\Setting\HeurekaFeedSettingEnum;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class HeurekaFeedItemTest extends TestCase
{
    private const int MOCKED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS = 5;

    private ProductPriceCalculationForCustomerUser|MockObject $productPriceCalculationForCustomerUserMock;

    private HeurekaProductDataBatchLoader|MockObject $heurekaProductDataBatchLoaderMock;

    private HeurekaCategoryFacade|MockObject $heurekaCategoryFacadeMock;

    private CategoryFacade|MockObject $categoryFacadeMock;

    private HeurekaFeedItemFactory $heurekaFeedItemFactory;

    private DomainConfig $defaultDomain;

    private Product|MockObject $defaultProduct;

    private ProductAvailabilityFacade|MockObject $productAvailabilityFacadeMock;

    private Setting|MockObject $settingMock;

    #[Override]
    protected function setUp(): void
    {
        $this->doSetUp(true);
    }

    /**
     * @param int $id
     * @param string $url
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private function createDomainConfigMock(int $id, string $url, string $locale): DomainConfig
    {
        $domainConfigMock = $this->createMock(DomainConfig::class);

        $domainConfigMock->method('getId')->willReturn($id);
        $domainConfigMock->method('getUrl')->willReturn($url);
        $domainConfigMock->method('getLocale')->willReturn($locale);

        return $domainConfigMock;
    }

    public function testMinimalHeurekaFeedItemIsCreatable(): void
    {
        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(1, $heurekaFeedItem->getId());
        self::assertEquals(1, $heurekaFeedItem->getSeekId());
        self::assertNull($heurekaFeedItem->getGroupId());
        self::assertEquals('product name', $heurekaFeedItem->getName());
        self::assertNull($heurekaFeedItem->getDescription());
        self::assertEquals('https://example.com/product-1', $heurekaFeedItem->getUrl());
        self::assertNull($heurekaFeedItem->getImgUrl());
        self::assertThat($heurekaFeedItem->getPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::zero()));
        self::assertThat($heurekaFeedItem->getPrice()->getPriceWithVat(), new IsMoneyEqual(Money::zero()));
        self::assertNull($heurekaFeedItem->getEan());
        self::assertEquals(0, $heurekaFeedItem->getDeliveryDate());
        self::assertNull($heurekaFeedItem->getManufacturer());
        self::assertNull($heurekaFeedItem->getCategoryText());
        self::assertEquals([], $heurekaFeedItem->getParams());
        self::assertNull($heurekaFeedItem->getCpc());
    }

    public function testHeurekaFeedItemWithGroupId(): void
    {
        $mainVariantMock = $this->createMock(Product::class);
        $mainVariantMock->method('getId')->willReturn(2);
        $this->defaultProduct->method('isVariant')->willReturn(true);
        $this->defaultProduct->method('getMainVariant')->willReturn($mainVariantMock);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(2, $heurekaFeedItem->getGroupId());
    }

    public function testHeurekaFeedItemWithDescription(): void
    {
        $this->defaultProduct->method('getDescriptionAsPlainText')
            ->with(1)->willReturn('product description');

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('product description', $heurekaFeedItem->getDescription());
    }

    public function testHeurekaFeedItemWithImgUrl(): void
    {
        $this->heurekaProductDataBatchLoaderMock->method('getProductImageUrl')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn('https://example.com/img/product/1');

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('https://example.com/img/product/1', $heurekaFeedItem->getImgUrl());
    }

    public function testHeurekaFeedItemWithEan(): void
    {
        $this->defaultProduct->method('getEan')->willReturn('1234567890123');

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('1234567890123', $heurekaFeedItem->getEan());
    }

    public function testHeurekaFeedItemWithManufacturer(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|\PHPUnit\Framework\MockObject\MockObject $brand */
        $brand = $this->createMock(Brand::class);
        $brand->method('getName')->willReturn('manufacturer name');
        $this->defaultProduct->method('getBrand')->willReturn($brand);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('manufacturer name', $heurekaFeedItem->getManufacturer());
    }

    public function testHeurekaFeedItemWithCategoryText(): void
    {
        /** @var \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory|\PHPUnit\Framework\MockObject\MockObject $heurekaCategoryMock */
        $heurekaCategoryMock = $this->createMock(HeurekaCategory::class);
        $heurekaCategoryMock->method('getFullName')->willReturn('heureka category full text');

        /** @var \Shopsys\FrameworkBundle\Model\Category\Category|\PHPUnit\Framework\MockObject\MockObject $categoryMock */
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getId')->willReturn(1);

        $this->categoryFacadeMock->method('findProductMainCategoryByDomainId')
            ->with($this->defaultProduct, $this->defaultDomain->getId())->willReturn($categoryMock);

        $this->heurekaCategoryFacadeMock->method('findByCategoryId')
            ->with(1)->willReturn($heurekaCategoryMock);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('heureka category full text', $heurekaFeedItem->getCategoryText());
    }

    public function testHeurekaFeedItemWithParams(): void
    {
        $this->heurekaProductDataBatchLoaderMock->method('getProductParametersByName')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn(['color' => 'black']);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(['color' => 'black'], $heurekaFeedItem->getParams());
    }

    public function testHeurekaFeedItemWithCpc(): void
    {
        $this->heurekaProductDataBatchLoaderMock->method('getProductCpc')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn(Money::create(5));

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertThat($heurekaFeedItem->getCpc(), new IsMoneyEqual(Money::create(5)));
    }

    public function testHeurekaFeedItemOutOfStock(): void
    {
        $this->doSetUp(false);
        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(self::MOCKED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS, $heurekaFeedItem->getDeliveryDate());
    }

    /**
     * @param bool $isProductAvailableOnDomain
     */
    private function doSetUp(bool $isProductAvailableOnDomain): void
    {
        $this->productPriceCalculationForCustomerUserMock = $this->createMock(
            ProductPriceCalculationForCustomerUser::class,
        );
        $this->heurekaProductDataBatchLoaderMock = $this->createMock(HeurekaProductDataBatchLoader::class);
        $this->heurekaCategoryFacadeMock = $this->createMock(HeurekaCategoryFacade::class);
        $this->categoryFacadeMock = $this->createMock(CategoryFacade::class);
        $this->productAvailabilityFacadeMock = $this->createMock(ProductAvailabilityFacade::class);
        $this->productAvailabilityFacadeMock->method('isProductAvailableOnDomainCached')->willReturn($isProductAvailableOnDomain);
        $this->settingMock = $this->createMock(Setting::class);

        if ($isProductAvailableOnDomain === false) {
            $this->settingMock->method('getForDomain')->with(HeurekaFeedSettingEnum::HEUREKA_FEED_DELIVERY_DAYS, Domain::FIRST_DOMAIN_ID)->willReturn(self::MOCKED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS);
        }

        $this->heurekaFeedItemFactory = new HeurekaFeedItemFactory(
            $this->productPriceCalculationForCustomerUserMock,
            $this->heurekaProductDataBatchLoaderMock,
            $this->heurekaCategoryFacadeMock,
            $this->categoryFacadeMock,
            $this->productAvailabilityFacadeMock,
            new InMemoryCache(),
            $this->settingMock,
        );

        $this->defaultDomain = $this->createDomainConfigMock(Domain::FIRST_DOMAIN_ID, 'https://example.cz', 'cs');

        $this->defaultProduct = $this->createMock(Product::class);
        $this->defaultProduct->method('getId')->willReturn(1);
        $this->defaultProduct->method('getFullName')->with('cs')->willReturn('product name');

        $productPrice = new ProductPrice(Price::zero(), $this->createMock(PricingGroup::class), false);
        $productPricesResult = new ProductPricesResult($productPrice, $productPrice);
        $this->productPriceCalculationForCustomerUserMock->method('calculatePricesForCustomerUserAndDomainId')
            ->with($this->defaultProduct, Domain::FIRST_DOMAIN_ID, null)->willReturn($productPricesResult);

        $this->heurekaProductDataBatchLoaderMock->method('getProductUrl')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn('https://example.com/product-1');
    }
}
