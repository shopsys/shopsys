<?php

namespace Tests\ReadModelBundle\Unit\Product\Detail;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\ReadModelBundle\Brand\BrandViewFacadeInterface;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Parameter\ParameterViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade;
use Shopsys\ReadModelBundle\Product\Detail\ProductDetailView;
use Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewFactory;

class ProductDetailViewFactoryTest extends TestCase
{
    private const MAIN_PAGE_DESCRIPTION = 'MainPageDescription';

    /**
     * @dataProvider getTestGetNameData
     * @param string|null $seoH1
     * @param string|null $name
     * @param string|null $expected
     */
    public function testGetName(
        ?string $seoH1,
        ?string $name,
        ?string $expected
    ): void {
        $productDetailView = $this->createProductDetailView(
            [
                'getSeoH1' => $seoH1,
                'getName' => $name,
            ]
        );

        self::assertSame($expected, $productDetailView->getName());
    }

    /**
     * @return array
     */
    public function getTestGetNameData(): array
    {
        return [
            [
                'seoH1' => 'seo title',
                'name' => 'title',
                'expected' => 'seo title',
            ], [
                'seoH1' => '',
                'name' => 'title',
                'expected' => 'title',
            ], [
                'seoH1' => null,
                'name' => 'title',
                'expected' => 'title',
            ], [
                'seoH1' => 'seo title',
                'name' => '',
                'expected' => 'seo title',
            ], [
                'seoH1' => 'seo title',
                'name' => null,
                'expected' => 'seo title',
            ], [
                'seoH1' => null,
                'name' => null,
                'expected' => null,
            ], [
                'seoH1' => '',
                'name' => '',
                'expected' => '',
            ],
        ];
    }

    /**
     * @dataProvider getTestNullableArguments
     * @param string|null $input
     * @param string|null $expected
     */
    public function testNullableArguments(
        ?string $input,
        ?string $expected
    ): void {
        $productDetailView = $this->createProductDetailView(
            [
                'getDescription' => $input,
                'getCatnum' => $input,
                'getEan' => $input,
                'getPartno' => $input,
            ]
        );

        self::assertSame($expected, $productDetailView->getDescription());
        self::assertSame($expected, $productDetailView->getCatnum());
        self::assertSame($expected, $productDetailView->getEan());
        self::assertSame($expected, $productDetailView->getPartno());
    }

    /**
     * @return array
     */
    public function getTestNullableArguments(): array
    {
        return [
            [
                'input' => 'some text',
                'expected' => 'some text',
            ], [
                'input' => null,
                'expected' => null,
            ],
        ];
    }

    /**
     * @dataProvider getTestGetAvailabilityAndInStock
     * @param string $availabilityString
     * @param int|null $dispatchTime
     * @param string $expectedAvailabilityString
     * @param bool $expectedInStockStatus
     */
    public function testGetAvailabilityAndInStock(
        string $availabilityString,
        ?int $dispatchTime,
        string $expectedAvailabilityString,
        bool $expectedInStockStatus
    ): void {
        $productAvailabilityMock = $this->createMock(Availability::class);
        $productAvailabilityMock->method('getName')->willReturn($availabilityString);
        $productAvailabilityMock->method('getDispatchTime')->willReturn($dispatchTime);

        $productDetailView = $this->createProductDetailView(
            [
                'getCalculatedAvailability' => $productAvailabilityMock,
            ]
        );

        self::assertSame($expectedAvailabilityString, $productDetailView->getAvailability());
        self::assertSame($expectedInStockStatus, $productDetailView->isInStock());
    }

    /**
     * @return array
     */
    public function getTestGetAvailabilityAndInStock(): array
    {
        return [
            [
                'availabilityString' => 'available',
                'dispatchTime' => 0,
                'expectedAvailabilityString' => 'available',
                'expectedInStockStatus' => true,
            ], [
                'availabilityString' => 'at the supplier',
                'dispatchTime' => 10,
                'expectedAvailabilityString' => 'at the supplier',
                'expectedInStockStatus' => false,
            ], [
                'availabilityString' => 'out of stock',
                'dispatchTime' => null,
                'expectedAvailabilityString' => 'out of stock',
                'expectedInStockStatus' => false,
            ],
        ];
    }

    /**
     * @dataProvider getTestVariants
     * @param bool $isMainVariant
     * @param bool $isVariant
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $mainVariantMock
     * @param int|null $expectedMainVariantId
     */
    public function testVariants(
        bool $isMainVariant,
        bool $isVariant,
        ?Product $mainVariantMock,
        ?int $expectedMainVariantId
    ): void {
        $productDetailView = $this->createProductDetailView(
            [
                'isMainVariant' => $isMainVariant,
                'isVariant' => $isVariant,
                'getMainVariant' => $mainVariantMock,
            ]
        );

        self::assertSame($isMainVariant, $productDetailView->isMainVariant());
        self::assertSame($isVariant, $productDetailView->isVariant());
        self::assertSame($expectedMainVariantId, $productDetailView->getMainVariantId());
    }

    /**
     * @return array
     */
    public function getTestVariants(): array
    {
        $mainVariantId = 5;

        $mainVariantMock = $this->createMock(Product::class);
        $mainVariantMock->method('getId')->willReturn($mainVariantId);

        return [
            [
                'isMainVariant' => true,
                'isVariant' => false,
                'mainVariantMock' => null,
                'expectedMainVariantId' => null,
            ], [
                'isMainVariant' => false,
                'isVariant' => false,
                'mainVariantMock' => null,
                'expectedMainVariantId' => null,
            ], [
                'isMainVariant' => false,
                'isVariant' => true,
                'mainVariantMock' => $mainVariantMock,
                'expectedMainVariantId' => $mainVariantId,
            ],
        ];
    }

    /**
     * @dataProvider getTestGetSeoMetaDescription
     * @param string|null $input
     * @param string|null $expected
     */
    public function testGetSeoMetaDescription(
        ?string $input,
        ?string $expected
    ): void {
        $productDetailView = $this->createProductDetailView(
            [
                'getSeoMetaDescription' => $input,
            ]
        );

        self::assertSame($expected, $productDetailView->getSeoMetaDescription());
    }

    /**
     * @return array
     */
    public function getTestGetSeoMetaDescription(): array
    {
        return [
            [
                'input' => 'some text',
                'expected' => 'some text',
            ], [
                'input' => null,
                'expected' => self::MAIN_PAGE_DESCRIPTION,
            ], [
                'input' => '',
                'expected' => '',
            ],
        ];
    }

    public function testProductPriceIsNull(): void
    {
        $productDetailView = $this->createProductDetailView(
            [],
            [],
            null
        );

        self::assertNull($productDetailView->getSellingPrice());
    }

    /**
     * @dataProvider getTestGetMainImageView
     * @param array $imageViews
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $expectedMainImageView
     */
    public function testGetMainImageView(
        array $imageViews,
        ?ImageView $expectedMainImageView
    ): void {
        $productDetailView = $this->createProductDetailView(
            [],
            $imageViews
        );

        self::assertSame($expectedMainImageView, $productDetailView->getMainImageView());
    }

    /**
     * @return array
     */
    public function getTestGetMainImageView(): array
    {
        $mainImageView = new ImageView(2, 'jpg', 'product', null);

        $imageViews = [
            $mainImageView,
            new ImageView(3, 'jpg', 'product', null),
            new ImageView(4, 'jpg', 'product', null),
            new ImageView(5, 'jpg', 'product', null),
        ];

        return [
            [
                'imageViews' => [],
                'expectedMainImageView' => null,
            ], [
                'imageViews' => $imageViews,
                'expectedMainImageView' => $mainImageView,
            ], [
                'imageViews' => [$mainImageView],
                'expectedMainImageView' => $mainImageView,
            ],
        ];
    }

    /**
     * @param array $productData
     * @param \Shopsys\ReadModelBundle\Image\ImageView[] $imageViews
     * @param int|null $priceAmount
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    private function createProductDetailView(
        array $productData,
        array $imageViews = [],
        ?int $priceAmount = 10
    ): ProductDetailView {
        $imageViewFacadeMock = $this->createImageViewFacadeMock($imageViews);
        $productActionViewFacadeMock = $this->createProductActionViewFacadeMock();
        $brandViewFacadeMock = $this->createBrandViewFacadeMock();
        $parameterViewFacadeMock = $this->createParameterViewFacadeMock();
        $productCachedAttributesFacadeMock = $this->createProductCachedAttributesFacadeMock($priceAmount);
        $categoryFacadeMock = $this->createCategoryFacadeMock();
        $domainMock = $this->createDomainMock();
        $seoSettingFacadeMock = $this->createSeoSettingFacadeMock();

        $productDetailViewFactory = new ProductDetailViewFactory(
            $imageViewFacadeMock,
            $productActionViewFacadeMock,
            $brandViewFacadeMock,
            $parameterViewFacadeMock,
            $domainMock,
            $productCachedAttributesFacadeMock,
            $categoryFacadeMock,
            $seoSettingFacadeMock
        );

        return $productDetailViewFactory->createFromProduct($this->createProductMock($productData));
    }

    /**
     * @param array $productData
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    private function createProductMock(array $productData): Product
    {
        $productMock = $this->createMock(Product::class);

        $productData = array_merge($this->getDefaultProductData(), $productData);

        foreach ($productData as $methodName => $value) {
            $productMock->method($methodName)->willReturn($value);
        }

        return $productMock;
    }

    /**
     * @return array
     */
    private function getDefaultProductData(): array
    {
        $productAvailabilityMock = $this->createMock(Availability::class);
        $productAvailabilityMock->method('getName')->willReturn('available');
        $productAvailabilityMock->method('getDispatchTime')->willReturn(0);

        return [
            'getCalculatedAvailability' => $productAvailabilityMock,
            'getCalculatedSellingDenied' => false,
            'getCatnum' => '',
            'getDescription' => '',
            'getEan' => '',
            'getFlags' => [],
            'getId' => 1,
            'getMainVariant' => null,
            'getName' => '',
            'getPartno' => '',
            'getSeoH1' => '',
            'getSeoMetaDescription' => '',
            'getSeoTitle' => '',
            'isMainVariant' => false,
            'isVariant' => false,
        ];
    }

    /**
     * @param \Shopsys\ReadModelBundle\Image\ImageView[] $imageViews
     * @return \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface
     */
    private function createImageViewFacadeMock(array $imageViews = []): ImageViewFacadeInterface
    {
        $imageViewFacadeMock = $this->createMock(ImageViewFacadeInterface::class);

        $imageViewFacadeMock->method('getAllImagesByEntityId')->willReturn($imageViews);

        return $imageViewFacadeMock;
    }

    /**
     * @return \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade
     */
    private function createProductActionViewFacadeMock(): ProductActionViewFacade
    {
        return $this->createMock(ProductActionViewFacade::class);
    }

    /**
     * @return \Shopsys\ReadModelBundle\Brand\BrandViewFacadeInterface
     */
    private function createBrandViewFacadeMock(): BrandViewFacadeInterface
    {
        return $this->createMock(BrandViewFacadeInterface::class);
    }

    /**
     * @return \Shopsys\ReadModelBundle\Parameter\ParameterViewFacadeInterface
     */
    private function createParameterViewFacadeMock(): ParameterViewFacadeInterface
    {
        return $this->createMock(ParameterViewFacadeInterface::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    private function createCategoryFacadeMock(): CategoryFacade
    {
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getId')->willReturn(1);

        $categoryFacade = $this->createMock(CategoryFacade::class);
        $categoryFacade->method('getProductMainCategoryOnCurrentDomain')->willReturn($categoryMock);

        return $categoryFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private function createDomainMock(): Domain
    {
        $domainMock = $this->createMock(Domain::class);
        $domainMock->method('getId')->willReturn(1);
        return $domainMock;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    private function createSeoSettingFacadeMock(): SeoSettingFacade
    {
        $seoSettingFacadeMock = $this->createMock(SeoSettingFacade::class);

        $seoSettingFacadeMock->method('getDescriptionMainPage')->willReturn(self::MAIN_PAGE_DESCRIPTION);

        return $seoSettingFacadeMock;
    }

    /**
     * @param int|null $priceAmount
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    private function createProductCachedAttributesFacadeMock(?int $priceAmount): ProductCachedAttributesFacade
    {
        $productCachedAttributesFacadeMock = $this->createMock(ProductCachedAttributesFacade::class);
        $getProductSellingPriceMethod = $productCachedAttributesFacadeMock->method('getProductSellingPrice');
        $getProductSellingPriceMethod->willReturn(
            $priceAmount === null ? null : $this->createProductPrice($priceAmount)
        );

        return $productCachedAttributesFacadeMock;
    }

    /**
     * @param int $amount
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    private function createProductPrice(int $amount): ProductPrice
    {
        return new ProductPrice(new Price(Money::create($amount), Money::create($amount)), false);
    }
}
