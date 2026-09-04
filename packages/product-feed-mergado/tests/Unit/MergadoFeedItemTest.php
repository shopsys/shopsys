<?php

declare(strict_types=1);

namespace Tests\ProductFeed\MergadoBundle\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductAdditionalServicesBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPricesResult;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItem;
use Shopsys\ProductFeed\MergadoBundle\Model\FeedItem\MergadoFeedItemFactory;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class MergadoFeedItemTest extends TestCase
{
    private const int MOCKED_SETTING_FEED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS = 8;

    private ProductPriceCalculationForCustomerUser|MockObject $productPriceCalculationForCustomerUserMock;

    private CurrencyFacade|MockObject $currencyFacadeMock;

    private ProductUrlsBatchLoader|MockObject $productUrlsBatchLoaderMock;

    private Stub|ProductAdditionalServicesBatchLoader $productAdditionalServicesBatchLoaderStub;

    private MergadoFeedItemFactory $mergadoFeedItemFactory;

    private DomainConfig $defaultDomain;

    private Product $defaultProduct;

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
     * @see \Tests\FrameworkBundle\Unit\Model\Product\Availability\ProductAvailabilityFacadeTest for the days/date derivation logic itself
     */
    public function testMergadoFeedItemDeliveryDaysAreTakenFromTheAvailabilityFacade(): void
    {
        $this->doSetUp(true);

        $productAvailabilityFacadeMock = $this->createMock(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeMock->expects($this->once())
            ->method('getProductAvailabilityDaysOrDateForFeedsByDomainId')
            ->with($this->defaultProduct, Domain::FIRST_DOMAIN_ID)
            ->willReturn('2026-07-26');

        $mergadoFeedItemFactory = $this->createMergadoFeedItemFactory($productAvailabilityFacadeMock);

        $mergadoFeedItem = $mergadoFeedItemFactory->createForProduct($this->defaultProduct, $this->defaultDomain);

        self::assertSame('2026-07-26', $mergadoFeedItem->getDeliveryDays());
    }

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
        self::assertNull($mergadoFeedItem->getBrand());
        self::assertSame([], $mergadoFeedItem->getFlags());
        self::assertSame([], $mergadoFeedItem->getParameters());
        self::assertSame([], $mergadoFeedItem->getGalleryImageUrls());
        self::assertNull($mergadoFeedItem->getMainVariantId());
        self::assertSame([], $mergadoFeedItem->getSpecialServices());
        self::assertSame([], $mergadoFeedItem->getZboziAdditionalServiceEntries());
    }

    public function testMergadoFeedItemWithAdditionalServices(): void
    {
        $this->doSetUp(true);

        $this->productAdditionalServicesBatchLoaderStub->method('getShownInFeedsSpecialServiceNames')
            ->willReturn(['Engraving']);
        $this->productAdditionalServicesBatchLoaderStub->method('getShownInFeedsZboziEntries')
            ->willReturn([
                [
                    'extraMessage' => 'custom',
                    'customText' => 'Custom engraving of the product',
                ],
            ]);

        $mergadoFeedItem = $this->mergadoFeedItemFactory->createForProduct($this->defaultProduct, $this->defaultDomain);

        self::assertSame(['Engraving'], $mergadoFeedItem->getSpecialServices());
        self::assertSame(
            [
                [
                    'extraMessage' => 'custom',
                    'customText' => 'Custom engraving of the product',
                ],
            ],
            $mergadoFeedItem->getZboziAdditionalServiceEntries(),
        );
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function doSetUp(bool $isProductAvailableOnStock): void
    {
        $this->productPriceCalculationForCustomerUserMock = $this->createMock(ProductPriceCalculationForCustomerUser::class);
        $this->currencyFacadeMock = $this->createMock(CurrencyFacade::class);
        $this->productUrlsBatchLoaderMock = $this->createMock(ProductUrlsBatchLoader::class);

        $productAvailabilityFacadeStub = $this->createStub(ProductAvailabilityFacade::class);
        $productAvailabilityFacadeStub->method('isProductAvailableOnDomainCached')->willReturn($isProductAvailableOnStock);
        $productAvailabilityFacadeStub->method('getProductAvailabilityDaysOrDateForFeedsByDomainId')->willReturn($isProductAvailableOnStock ? 0 : self::MOCKED_SETTING_FEED_DELIVERY_DAYS_FOR_OUT_OF_STOCK_PRODUCTS);

        $this->productAdditionalServicesBatchLoaderStub = $this->createStub(ProductAdditionalServicesBatchLoader::class);

        $this->mergadoFeedItemFactory = $this->createMergadoFeedItemFactory($productAvailabilityFacadeStub);

        $defaultCurrency = $this->createCurrencyStub(1, 'EUR');
        $this->defaultDomain = $this->createDomainConfigStub(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            'en',
            $defaultCurrency,
        );

        $this->defaultProduct = $this->createStub(Product::class);
        $this->defaultProduct->method('getId')->willReturn(1);
        $this->defaultProduct->method('getCatnum')->willReturn('catnum');
        $this->defaultProduct->method('getFullName')->willReturn('product name');
        $this->defaultProduct->method('getDescription')->willReturn('description');
        $this->defaultProduct->method('getShortDescriptionUsp1')->willReturn('short description usp 1');

        $this->mockProductPrice($this->defaultProduct, $this->defaultDomain, Price::zero());
        $this->mockProductUrl($this->defaultProduct, $this->defaultDomain, 'https://example.com/product-1');
        $this->mockProductImageUrl($this->defaultProduct, $this->defaultDomain, 'https://example.com/img/product/1');
    }

    private function createMergadoFeedItemFactory(
        ProductAvailabilityFacade $productAvailabilityFacade,
    ): MergadoFeedItemFactory {
        $categoryFacadeStub = $this->createStub(CategoryFacade::class);
        $categoryFacadeStub->method('getCategoryNamesInPathFromRootToProductMainCategoryOnDomain')->willReturn(['category1', 'category2']);

        return new MergadoFeedItemFactory(
            $this->productUrlsBatchLoaderMock,
            $this->createStub(ProductParametersBatchLoader::class),
            $categoryFacadeStub,
            $productAvailabilityFacade,
            $this->productPriceCalculationForCustomerUserMock,
            $this->createStub(ImageFacade::class),
            $this->currencyFacadeMock,
            $this->createStub(LoggerInterface::class),
            $this->productAdditionalServicesBatchLoaderStub,
        );
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

    private function mockProductPrice(Product $product, DomainConfig $domain, Price $price): void
    {
        $domainId = $domain->getId();
        $pricingGroup = new PricingGroup(new PricingGroupData(), $domainId);
        $productPrice = new ProductPrice($price, $pricingGroup, false);
        $productPricesResult = new ProductPricesResult($productPrice, $productPrice);

        $this->productPriceCalculationForCustomerUserMock->expects($this->any())->method('calculatePricesForCustomerUserAndDomainId')
            ->with($product, $domainId, null)->willReturn($productPricesResult);
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
}
