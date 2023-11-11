<?php

declare(strict_types=1);

namespace Tests\ProductFeed\HeurekaBundle\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItem;
use Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFactory;
use Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaProductDataBatchLoader;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategory;
use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class HeurekaFeedItemTest extends TestCase
{
    private ProductPriceCalculationForCustomerUser|MockObject $productPriceCalculationForCustomerUserMock;

    private HeurekaProductDataBatchLoader|MockObject $heurekaProductDataBatchLoaderMock;

    private HeurekaCategoryFacade|MockObject $heurekaCategoryFacadeMock;

    private CategoryFacade|MockObject $categoryFacadeMock;

    private HeurekaFeedItemFactory $heurekaFeedItemFactory;

    private DomainConfig $defaultDomain;

    private Product|MockObject $defaultProduct;

    protected function setUp(): void
    {
        $this->productPriceCalculationForCustomerUserMock = $this->createMock(
            ProductPriceCalculationForCustomerUser::class,
        );
        $this->heurekaProductDataBatchLoaderMock = $this->createMock(HeurekaProductDataBatchLoader::class);
        $this->heurekaCategoryFacadeMock = $this->createMock(HeurekaCategoryFacade::class);
        $this->categoryFacadeMock = $this->createMock(CategoryFacade::class);
        $productAvailabilityFacadeMock = $this->createMock(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeMock->method('getProductAvailabilityDaysByDomainId')->willReturn(0);

        $this->heurekaFeedItemFactory = new HeurekaFeedItemFactory(
            $this->productPriceCalculationForCustomerUserMock,
            $this->heurekaProductDataBatchLoaderMock,
            $this->heurekaCategoryFacadeMock,
            $this->categoryFacadeMock,
            $productAvailabilityFacadeMock,
        );

        $this->defaultDomain = $this->createDomainConfigMock(Domain::FIRST_DOMAIN_ID, 'https://example.cz', 'cs');

        $this->defaultProduct = $this->createMock(Product::class);
        $this->defaultProduct->method('getId')->willReturn(1);
        $this->defaultProduct->method('getName')->with('cs')->willReturn('product name');

        $productPrice = new ProductPrice(Price::zero(), false);
        $this->productPriceCalculationForCustomerUserMock->method('calculatePriceForCustomerUserAndDomainId')
            ->with($this->defaultProduct, Domain::FIRST_DOMAIN_ID, null)->willReturn($productPrice);

        $this->heurekaProductDataBatchLoaderMock->method('getProductUrl')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn('https://example.com/product-1');
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

    public function testMinimalHeurekaFeedItemIsCreatable()
    {
        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertInstanceOf(HeurekaFeedItem::class, $heurekaFeedItem);

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

    public function testHeurekaFeedItemWithGroupId()
    {
        $mainVariantMock = $this->createMock(Product::class);
        $mainVariantMock->method('getId')->willReturn(2);
        $this->defaultProduct->method('isVariant')->willReturn(true);
        $this->defaultProduct->method('getMainVariant')->willReturn($mainVariantMock);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(2, $heurekaFeedItem->getGroupId());
    }

    public function testHeurekaFeedItemWithDescription()
    {
        $this->defaultProduct->method('getDescription')
            ->with(1)->willReturn('product description');

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('product description', $heurekaFeedItem->getDescription());
    }

    public function testHeurekaFeedItemWithImgUrl()
    {
        $this->heurekaProductDataBatchLoaderMock->method('getProductImageUrl')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn('https://example.com/img/product/1');

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('https://example.com/img/product/1', $heurekaFeedItem->getImgUrl());
    }

    public function testHeurekaFeedItemWithEan()
    {
        $this->defaultProduct->method('getEan')->willReturn('1234567890123');

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('1234567890123', $heurekaFeedItem->getEan());
    }

    public function testHeurekaFeedItemWithManufacturer()
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|\PHPUnit\Framework\MockObject\MockObject $brand */
        $brand = $this->createMock(Brand::class);
        $brand->method('getName')->willReturn('manufacturer name');
        $this->defaultProduct->method('getBrand')->willReturn($brand);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals('manufacturer name', $heurekaFeedItem->getManufacturer());
    }

    public function testHeurekaFeedItemWithCategoryText()
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

    public function testHeurekaFeedItemWithParams()
    {
        $this->heurekaProductDataBatchLoaderMock->method('getProductParametersByName')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn(['color' => 'black']);

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertEquals(['color' => 'black'], $heurekaFeedItem->getParams());
    }

    public function testHeurekaFeedItemWithCpc()
    {
        $this->heurekaProductDataBatchLoaderMock->method('getProductCpc')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn(Money::create(5));

        $heurekaFeedItem = $this->heurekaFeedItemFactory->create($this->defaultProduct, $this->defaultDomain);

        self::assertThat($heurekaFeedItem->getCpc(), new IsMoneyEqual(Money::create(5)));
    }
}
