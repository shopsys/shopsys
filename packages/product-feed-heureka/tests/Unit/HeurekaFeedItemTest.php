<?php

declare(strict_types=1);

namespace Tests\ProductFeed\HeurekaBundle\Unit;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
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
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
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

    private Stub|HeurekaCategoryFacade $heurekaCategoryFacadeStub;

    private Stub|CategoryFacade $categoryFacadeStub;

    private HeurekaFeedItemFactory $heurekaFeedItemFactory;

    private DomainConfig $defaultDomain;

    private Product|MockObject $defaultProduct;

    private ProductAvailabilityFacade $productAvailabilityFacadeStub;

    private Stub|Setting $settingStub;

    #[Override]
    protected function setUp(): void
    {
        $this->doSetUp(true);
    }

    private function createDomainConfigStub(int $id, string $url, string $locale): DomainConfig
    {
        $domainConfigStub = $this->createStub(DomainConfig::class);

        $domainConfigStub->method('getId')->willReturn($id);
        $domainConfigStub->method('getUrl')->willReturn($url);
        $domainConfigStub->method('getLocale')->willReturn($locale);

        return $domainConfigStub;
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
        $mainVariantStub = $this->createStub(Product::class);
        $mainVariantStub->method('getId')->willReturn(2);
        $this->defaultProduct->expects($this->any())->method('isVariant')->willReturn(true);
        $this->defaultProduct->expects($this->any())->method('getMainVariant')->willReturn($mainVariantStub);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(2, $heurekaFeedItem->getGroupId());
    }

    public function testHeurekaFeedItemWithDescription(): void
    {
        $this->defaultProduct->expects($this->any())->method('getDescriptionAsPlainText')
            ->with(1)->willReturn('product description');

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('product description', $heurekaFeedItem->getDescription());
    }

    public function testHeurekaFeedItemWithImgUrl(): void
    {
        $this->heurekaProductDataBatchLoaderMock->expects($this->any())->method('getProductImageUrl')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn('https://example.com/img/product/1');

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('https://example.com/img/product/1', $heurekaFeedItem->getImgUrl());
    }

    public function testHeurekaFeedItemWithEan(): void
    {
        $this->defaultProduct->expects($this->any())->method('getEan')->willReturn('1234567890123');

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('1234567890123', $heurekaFeedItem->getEan());
    }

    public function testHeurekaFeedItemWithManufacturer(): void
    {
        $brand = $this->createStub(Brand::class);
        $brand->method('getName')->willReturn('manufacturer name');
        $this->defaultProduct->expects($this->any())->method('getBrand')->willReturn($brand);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('manufacturer name', $heurekaFeedItem->getManufacturer());
    }

    public function testHeurekaFeedItemWithCategoryText(): void
    {
        $heurekaCategoryStub = $this->createStub(HeurekaCategory::class);
        $heurekaCategoryStub->method('getFullName')->willReturn('heureka category full text');

        $categoryStub = $this->createStub(Category::class);
        $categoryStub->method('getId')->willReturn(1);

        $this->categoryFacadeStub->method('findProductMainCategoryByDomainId')
            ->willReturnMap([
                [$this->defaultProduct, $this->defaultDomain->getId(), $categoryStub],
            ]);

        $this->heurekaCategoryFacadeStub->method('findByCategoryIdAndLocale')
            ->willReturnMap([
                [1, 'cs', $heurekaCategoryStub],
            ]);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('heureka category full text', $heurekaFeedItem->getCategoryText());
    }

    public function testHeurekaFeedItemWithParams(): void
    {
        $this->heurekaProductDataBatchLoaderMock->expects($this->any())->method('getProductParametersByName')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn(['color' => 'black']);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(['color' => 'black'], $heurekaFeedItem->getParams());
    }

    public function testHeurekaFeedItemWithCpc(): void
    {
        $this->heurekaProductDataBatchLoaderMock->expects($this->any())->method('getProductCpc')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn(Money::create(5));

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertThat($heurekaFeedItem->getCpc(), new IsMoneyEqual(Money::create(5)));
    }

    /**
     * @return iterable<string, array{isProductAvailableOnDomain: bool, daysUntilExpectedRestocking: int|null, heurekaFeedDeliveryDays: int|null, expectedDeliveryDate: int|null}>
     */
    public static function getDeliveryDateData(): iterable
    {
        yield 'product in stock' => [
            'isProductAvailableOnDomain' => true,
            'daysUntilExpectedRestocking' => null,
            'heurekaFeedDeliveryDays' => self::MOCKED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS,
            'expectedDeliveryDate' => 0,
        ];

        yield 'out of stock with expected restocking date' => [
            'isProductAvailableOnDomain' => false,
            'daysUntilExpectedRestocking' => 10,
            'heurekaFeedDeliveryDays' => self::MOCKED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS,
            'expectedDeliveryDate' => 10,
        ];

        yield 'out of stock without a date falls back to the setting' => [
            'isProductAvailableOnDomain' => false,
            'daysUntilExpectedRestocking' => null,
            'heurekaFeedDeliveryDays' => self::MOCKED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS,
            'expectedDeliveryDate' => self::MOCKED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS,
        ];

        yield 'out of stock without a date and without the setting stays empty' => [
            'isProductAvailableOnDomain' => false,
            'daysUntilExpectedRestocking' => null,
            'heurekaFeedDeliveryDays' => null,
            'expectedDeliveryDate' => null,
        ];
    }

    #[DataProvider('getDeliveryDateData')]
    public function testHeurekaFeedItemDeliveryDate(
        bool $isProductAvailableOnDomain,
        ?int $daysUntilExpectedRestocking,
        ?int $heurekaFeedDeliveryDays,
        ?int $expectedDeliveryDate,
    ): void {
        $this->doSetUp($isProductAvailableOnDomain, $daysUntilExpectedRestocking, $heurekaFeedDeliveryDays);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertSame($expectedDeliveryDate, $heurekaFeedItem->getDeliveryDate());
    }

    private function doSetUp(
        bool $isProductAvailableOnDomain,
        ?int $daysUntilExpectedRestocking = null,
        ?int $heurekaFeedDeliveryDays = self::MOCKED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS,
    ): void {
        $this->productPriceCalculationForCustomerUserMock = $this->createMock(
            ProductPriceCalculationForCustomerUser::class,
        );
        $this->heurekaProductDataBatchLoaderMock = $this->createMock(HeurekaProductDataBatchLoader::class);
        $this->heurekaCategoryFacadeStub = $this->createStub(HeurekaCategoryFacade::class);
        $this->categoryFacadeStub = $this->createStub(CategoryFacade::class);
        $this->productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $this->productAvailabilityFacadeStub->method('isProductAvailableOnDomainCached')->willReturn($isProductAvailableOnDomain);
        $this->productAvailabilityFacadeStub->method('findDaysUntilExpectedRestocking')->willReturn($daysUntilExpectedRestocking);
        $this->settingStub = $this->createStub(Setting::class);
        $this->settingStub->method('getForDomain')->willReturnMap([
            [HeurekaFeedSettingEnum::HEUREKA_FEED_DELIVERY_DAYS, Domain::FIRST_DOMAIN_ID, $heurekaFeedDeliveryDays],
        ]);

        $this->heurekaFeedItemFactory = new HeurekaFeedItemFactory(
            $this->createStub(TransportFacade::class),
            $this->productPriceCalculationForCustomerUserMock,
            $this->heurekaProductDataBatchLoaderMock,
            $this->heurekaCategoryFacadeStub,
            $this->categoryFacadeStub,
            $this->productAvailabilityFacadeStub,
            new InMemoryCache(),
            $this->settingStub,
        );

        $this->defaultDomain = $this->createDomainConfigStub(Domain::FIRST_DOMAIN_ID, 'https://example.cz', 'cs');

        $this->defaultProduct = $this->createMock(Product::class);
        $this->defaultProduct->expects($this->any())->method('getId')->willReturn(1);
        $this->defaultProduct->expects($this->any())->method('getFullName')->with('cs')->willReturn('product name');

        $productPrice = new ProductPrice(Price::zero(), $this->createStub(PricingGroup::class), false);
        $productPricesResult = new ProductPricesResult($productPrice, $productPrice);
        $this->productPriceCalculationForCustomerUserMock->expects($this->any())->method('calculatePricesForCustomerUserAndDomainId')
            ->with($this->defaultProduct, Domain::FIRST_DOMAIN_ID, null)->willReturn($productPricesResult);

        $this->heurekaProductDataBatchLoaderMock->expects($this->any())->method('getProductUrl')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn('https://example.com/product-1');
    }
}
