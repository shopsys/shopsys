<?php

declare(strict_types=1);

namespace Tests\ProductFeed\ZboziBundle\Unit;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPricesResult;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\ZboziBundle\Model\FeedItem\ZboziFeedItemFactory;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomainData;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class ZboziFeedItemTest extends TestCase
{
    private ProductPriceCalculationForCustomerUser|MockObject $productPriceCalculationForCustomerUserMock;

    private ProductUrlsBatchLoader|MockObject $productUrlsBatchLoaderMock;

    private Stub|ProductParametersBatchLoader $productParametersBatchLoaderStub;

    private CategoryFacade|MockObject $categoryFacadeMock;

    private ZboziFeedItemFactory $zboziFeedItemFactory;

    private DomainConfig $defaultDomain;

    private Product|MockObject $defaultProduct;

    #[Override]
    protected function setUp(): void
    {
        $this->productPriceCalculationForCustomerUserMock = $this->createMock(
            ProductPriceCalculationForCustomerUser::class,
        );
        $this->productUrlsBatchLoaderMock = $this->createMock(ProductUrlsBatchLoader::class);
        $this->productParametersBatchLoaderStub = $this->createStub(ProductParametersBatchLoader::class);
        $this->categoryFacadeMock = $this->createMock(CategoryFacade::class);
        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('getProductAvailabilityDaysForFeedsByDomainId')->willReturn(0);

        $this->zboziFeedItemFactory = new ZboziFeedItemFactory(
            $this->productPriceCalculationForCustomerUserMock,
            $this->productUrlsBatchLoaderMock,
            $this->productParametersBatchLoaderStub,
            $this->categoryFacadeMock,
            $productAvailabilityFacadeStub,
        );

        $this->defaultDomain = $this->createDomainConfigStub(Domain::FIRST_DOMAIN_ID, 'https://example.cz', 'cs');

        $this->defaultProduct = $this->createMock(Product::class);
        $this->defaultProduct->expects($this->any())->method('getId')->willReturn(1);
        $this->defaultProduct->expects($this->any())->method('getFullName')->with('cs')->willReturn('product name');

        $productPrice = new ProductPrice(Price::zero(), $this->createStub(PricingGroup::class), false);
        $productPricesResult = new ProductPricesResult($productPrice, $productPrice);
        $this->productPriceCalculationForCustomerUserMock->expects($this->any())->method('calculatePricesForCustomerUserAndDomainId')
            ->with($this->defaultProduct, Domain::FIRST_DOMAIN_ID, null)->willReturn($productPricesResult);

        $this->productUrlsBatchLoaderMock->expects($this->any())->method('getProductUrl')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn('https://example.com/product-1');

        $this->categoryFacadeMock->expects($this->any())->method('getCategoryNamesInPathFromRootToProductMainCategoryOnDomain')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn(
                ['category A', 'category B', 'category C'],
            );
    }

    private function createDomainConfigStub(int $id, string $url, string $locale): DomainConfig
    {
        $domainConfigStub = $this->createStub(DomainConfig::class);

        $domainConfigStub->method('getId')->willReturn($id);
        $domainConfigStub->method('getUrl')->willReturn($url);
        $domainConfigStub->method('getLocale')->willReturn($locale);

        return $domainConfigStub;
    }

    public function testMinimalZboziFeedItemIsCreatable(): void
    {
        $zboziFeedItem = $this->zboziFeedItemFactory->create($this->defaultProduct, null, $this->defaultDomain);

        self::assertEquals(1, $zboziFeedItem->getId());
        self::assertEquals(1, $zboziFeedItem->getSeekId());
        self::assertNull($zboziFeedItem->getGroupId());
        self::assertEquals('product name', $zboziFeedItem->getName());
        self::assertNull($zboziFeedItem->getDescription());
        self::assertEquals('https://example.com/product-1', $zboziFeedItem->getUrl());
        self::assertNull($zboziFeedItem->getImgUrl());
        self::assertThat($zboziFeedItem->getPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::zero()));
        self::assertThat($zboziFeedItem->getPrice()->getPriceWithVat(), new IsMoneyEqual(Money::zero()));
        self::assertNull($zboziFeedItem->getEan());
        self::assertNull($zboziFeedItem->getProductno());
        self::assertEquals(0, $zboziFeedItem->getDeliveryDate());
        self::assertNull($zboziFeedItem->getManufacturer());
        self::assertEquals('category A | category B | category C', $zboziFeedItem->getCategoryText());
        self::assertEquals([], $zboziFeedItem->getParams());
        self::assertNull($zboziFeedItem->getMaxCpc());
        self::assertNull($zboziFeedItem->getMaxCpcSearch());
    }

    public function testZboziFeedItemWithGroupId(): void
    {
        $mainVariantStub = $this->createStub(Product::class);
        $mainVariantStub->method('getId')->willReturn(2);
        $this->defaultProduct->expects($this->any())->method('isVariant')->willReturn(true);
        $this->defaultProduct->expects($this->any())->method('getMainVariant')->willReturn($mainVariantStub);

        $zboziFeedItem = $this->zboziFeedItemFactory->create($this->defaultProduct, null, $this->defaultDomain);

        self::assertEquals(2, $zboziFeedItem->getGroupId());
    }

    public function testZboziFeedItemWithDescription(): void
    {
        $this->defaultProduct->expects($this->any())->method('getDescriptionAsPlainText')
            ->with(1)->willReturn('product description');

        $zboziFeedItem = $this->zboziFeedItemFactory->create($this->defaultProduct, null, $this->defaultDomain);

        self::assertEquals('product description', $zboziFeedItem->getDescription());
    }

    public function testZboziFeedItemWithImgUrl(): void
    {
        $this->productUrlsBatchLoaderMock->expects($this->any())->method('getProductImageUrl')
            ->with($this->defaultProduct, $this->defaultDomain)->willReturn('https://example.com/img/product/1');

        $zboziFeedItem = $this->zboziFeedItemFactory->create($this->defaultProduct, null, $this->defaultDomain);

        self::assertEquals('https://example.com/img/product/1', $zboziFeedItem->getImgUrl());
    }

    public function testZboziFeedItemWithEan(): void
    {
        $this->defaultProduct->expects($this->any())->method('getEan')->willReturn('1234567890123');

        $zboziFeedItem = $this->zboziFeedItemFactory->create($this->defaultProduct, null, $this->defaultDomain);

        self::assertEquals('1234567890123', $zboziFeedItem->getEan());
    }

    public function testZboziFeedItemWithProductno(): void
    {
        $this->defaultProduct->expects($this->any())->method('getPartno')->willReturn('PN01-B');

        $zboziFeedItem = $this->zboziFeedItemFactory->create($this->defaultProduct, null, $this->defaultDomain);

        self::assertEquals('PN01-B', $zboziFeedItem->getProductno());
    }

    public function testZboziFeedItemWithManufacturer(): void
    {
        $brand = $this->createStub(Brand::class);
        $brand->method('getName')->willReturn('manufacturer name');
        $this->defaultProduct->expects($this->any())->method('getBrand')->willReturn($brand);

        $zboziFeedItem = $this->zboziFeedItemFactory->create($this->defaultProduct, null, $this->defaultDomain);

        self::assertEquals('manufacturer name', $zboziFeedItem->getManufacturer());
    }

    public function testZboziFeedItemWithParams(): void
    {
        $this->productParametersBatchLoaderStub->method('getProductParametersByName')
            ->willReturnMap([
                [$this->defaultProduct, $this->defaultDomain, ['color' => 'black']],
            ]);

        $zboziFeedItem = $this->zboziFeedItemFactory->create($this->defaultProduct, null, $this->defaultDomain);

        self::assertEquals(['color' => 'black'], $zboziFeedItem->getParams());
    }

    public function testZboziFeedItemWithMaxCpc(): void
    {
        $zboziProductDomainData = new ZboziProductDomainData();
        $zboziProductDomainData->cpc = Money::create('5.0');
        $zboziProductDomainData->product = $this->defaultProduct;
        $zboziProductDomain = new ZboziProductDomain($zboziProductDomainData);

        $zboziFeedItem = $this->zboziFeedItemFactory->create(
            $this->defaultProduct,
            $zboziProductDomain,
            $this->defaultDomain,
        );

        self::assertThat($zboziFeedItem->getMaxCpc(), new IsMoneyEqual(Money::create(5)));
        self::assertNull($zboziFeedItem->getMaxCpcSearch());
    }

    public function testZboziFeedItemWithMaxCpcSearch(): void
    {
        $zboziProductDomainData = new ZboziProductDomainData();
        $zboziProductDomainData->cpcSearch = Money::create('5.0');
        $zboziProductDomainData->product = $this->defaultProduct;
        $zboziProductDomain = new ZboziProductDomain($zboziProductDomainData);

        $zboziFeedItem = $this->zboziFeedItemFactory->create(
            $this->defaultProduct,
            $zboziProductDomain,
            $this->defaultDomain,
        );

        self::assertNull($zboziFeedItem->getMaxCpc());
        self::assertThat($zboziFeedItem->getMaxCpcSearch(), new IsMoneyEqual(Money::create(5)));
    }
}
