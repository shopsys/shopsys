<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Filter;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ProductFilterConfigFactoryTest extends TestCase
{
    private const string LOCALE = 'en';

    private const int CATEGORY_ID = 1;

    private const int BRAND_LISTING_BRAND_ID = 5;

    private PricingGroup $pricingGroup;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->pricingGroup = $this->createStub(PricingGroup::class);
    }

    public function testConfigsOfAllListingsAreBuiltFromOneLookupPerKind(): void
    {
        $category = $this->createCategoryStub(self::CATEGORY_ID);
        $brand = $this->createBrandStub(self::BRAND_LISTING_BRAND_ID);
        $flag7 = $this->createFlagStub(7);
        $flag9 = $this->createFlagStub(9);
        $brand5 = $this->createBrandStub(5);
        $brand6 = $this->createBrandStub(6);
        $parameterFilterChoice = new ParameterFilterChoice($this->createParameterStub(3));
        $categoryPriceRange = $this->createPriceRange('10');
        $brandPriceRange = $this->createPriceRange('20');
        $searchPriceRange = $this->createPriceRange('30');

        $batchLoadData = [
            'category' => new ProductFilterBatchLoadData($category, ''),
            'brand' => new ProductFilterBatchLoadData($brand, ''),
            'search' => new ProductFilterBatchLoadData(null, 'phone'),
        ];

        $productFilterElasticFacade = $this->createMock(ProductFilterElasticFacade::class);
        $productFilterElasticFacade->expects($this->once())
            ->method('getProductFilterConfigIdsDataByBatchLoadData')
            ->with($batchLoadData, $this->pricingGroup)
            ->willReturn([
                'category' => new ProductFilterConfigIdsData([
                    3 => [30, 31],
                ], [7, 9], [5, 6], $categoryPriceRange),
                'brand' => new ProductFilterConfigIdsData([], [9], [5], $brandPriceRange),
                'search' => new ProductFilterConfigIdsData([], [7], [6], $searchPriceRange),
            ]);

        $flagFacade = $this->createMock(FlagFacade::class);
        $flagFacade->expects($this->once())
            ->method('getVisibleFlagsByIds')
            ->with([7, 9], self::LOCALE)
            ->willReturn([$flag9, $flag7]);

        $brandFacade = $this->createMock(BrandFacade::class);
        $brandFacade->expects($this->once())
            ->method('getBrandsByIds')
            ->with([5, 6])
            ->willReturn([$brand6, $brand5]);

        $parameterFacade = $this->createMock(ParameterFacade::class);
        $parameterFacade->expects($this->once())
            ->method('getParameterFilterChoicesByIdsIndexedByKey')
            ->with(['category' => [3 => [30, 31]]], self::LOCALE)
            ->willReturn(['category' => [$parameterFilterChoice]]);
        $parameterFacade->expects($this->once())
            ->method('getParameterIdsSortedByPositionIndexedByCategoryId')
            ->with([$category])
            ->willReturn([self::CATEGORY_ID => [3]]);

        $productFilterConfigFactory = $this->createProductFilterConfigFactory($productFilterElasticFacade, $parameterFacade, $flagFacade, $brandFacade);
        $productFilterConfigsIndexedByKey = $productFilterConfigFactory->createByBatchLoadData($batchLoadData, self::LOCALE);

        $this->assertSame(['category', 'brand', 'search'], array_keys($productFilterConfigsIndexedByKey));

        $this->assertConfig($productFilterConfigsIndexedByKey['category'], [$parameterFilterChoice], [$flag9, $flag7], [$brand6, $brand5], $categoryPriceRange);
        $this->assertConfig($productFilterConfigsIndexedByKey['brand'], [], [$flag9], [], $brandPriceRange);
        $this->assertConfig($productFilterConfigsIndexedByKey['search'], [], [$flag7], [$brand6], $searchPriceRange);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $expectedParameterChoices
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $expectedFlagChoices
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[] $expectedBrandChoices
     */
    private function assertConfig(
        ProductFilterConfig $productFilterConfig,
        array $expectedParameterChoices,
        array $expectedFlagChoices,
        array $expectedBrandChoices,
        PriceRange $expectedPriceRange,
    ): void {
        $this->assertSame($expectedParameterChoices, $productFilterConfig->getParameterChoices());
        $this->assertSame($expectedFlagChoices, $productFilterConfig->getFlagChoices());
        $this->assertSame($expectedBrandChoices, $productFilterConfig->getBrandChoices());
        $this->assertSame($expectedPriceRange, $productFilterConfig->getPriceRange());
    }

    public function testCategoryParameterChoicesFollowCategoryPositionsAndSkipUnknownParameters(): void
    {
        $category = $this->createCategoryStub(self::CATEGORY_ID);
        $parameterFilterChoice3 = new ParameterFilterChoice($this->createParameterStub(3));
        $parameterFilterChoice4 = new ParameterFilterChoice($this->createParameterStub(4));
        $batchLoadData = ['category' => new ProductFilterBatchLoadData($category, '')];

        $productFilterElasticFacade = $this->createStub(ProductFilterElasticFacade::class);
        $productFilterElasticFacade->method('getProductFilterConfigIdsDataByBatchLoadData')
            ->willReturn(['category' => new ProductFilterConfigIdsData([3 => [30], 4 => [40]], [], [], $this->createPriceRange('10'))]);

        $flagFacade = $this->createMock(FlagFacade::class);
        $flagFacade->expects($this->never())->method('getVisibleFlagsByIds');

        $brandFacade = $this->createMock(BrandFacade::class);
        $brandFacade->expects($this->never())->method('getBrandsByIds');

        $parameterFacade = $this->createStub(ParameterFacade::class);
        $parameterFacade->method('getParameterFilterChoicesByIdsIndexedByKey')
            ->willReturn(['category' => [$parameterFilterChoice3, $parameterFilterChoice4]]);
        $parameterFacade->method('getParameterIdsSortedByPositionIndexedByCategoryId')
            ->willReturn([self::CATEGORY_ID => [4, 8, 3]]);

        $productFilterConfigFactory = $this->createProductFilterConfigFactory($productFilterElasticFacade, $parameterFacade, $flagFacade, $brandFacade);
        $productFilterConfigsIndexedByKey = $productFilterConfigFactory->createByBatchLoadData($batchLoadData, self::LOCALE);

        $this->assertSame(
            [$parameterFilterChoice4, $parameterFilterChoice3],
            $productFilterConfigsIndexedByKey['category']->getParameterChoices(),
        );
    }

    public function testEmptyBatchNeedsNoLookup(): void
    {
        $productFilterElasticFacade = $this->createMock(ProductFilterElasticFacade::class);
        $productFilterElasticFacade->expects($this->never())->method('getProductFilterConfigIdsDataByBatchLoadData');

        $productFilterConfigFactory = $this->createProductFilterConfigFactory(
            $productFilterElasticFacade,
            $this->createStub(ParameterFacade::class),
            $this->createStub(FlagFacade::class),
            $this->createStub(BrandFacade::class),
        );

        $this->assertSame([], $productFilterConfigFactory->createByBatchLoadData([], self::LOCALE));
    }

    private function createProductFilterConfigFactory(
        ProductFilterElasticFacade $productFilterElasticFacade,
        ParameterFacade $parameterFacade,
        FlagFacade $flagFacade,
        BrandFacade $brandFacade,
    ): ProductFilterConfigFactory {
        $currentCustomerUser = $this->createStub(CurrentCustomerUser::class);
        $currentCustomerUser->method('getPricingGroup')->willReturn($this->pricingGroup);

        return new ProductFilterConfigFactory(
            $currentCustomerUser,
            $productFilterElasticFacade,
            $parameterFacade,
            $flagFacade,
            $brandFacade,
        );
    }

    private function createCategoryStub(int $id): Category
    {
        $category = $this->createStub(Category::class);
        $category->method('getId')->willReturn($id);

        return $category;
    }

    private function createBrandStub(int $id): Brand
    {
        $brand = $this->createStub(Brand::class);
        $brand->method('getId')->willReturn($id);

        return $brand;
    }

    private function createFlagStub(int $id): Flag
    {
        $flag = $this->createStub(Flag::class);
        $flag->method('getId')->willReturn($id);

        return $flag;
    }

    private function createParameterStub(int $id): Parameter
    {
        $parameter = $this->createStub(Parameter::class);
        $parameter->method('getId')->willReturn($id);

        return $parameter;
    }

    private function createPriceRange(string $maximalPrice): PriceRange
    {
        return new PriceRange(Money::zero(), Money::create($maximalPrice));
    }
}
