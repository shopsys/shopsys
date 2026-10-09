<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Product\Filter;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsBatchLoader;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory;

class ProductFilterOptionsBatchLoaderTest extends TestCase
{
    private const int CATEGORY_ID = 1;

    private SyncPromiseAdapter $promiseAdapter;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->promiseAdapter = new SyncPromiseAdapter();
    }

    public function testDisabledModuleReturnsEmptyOptionsWithoutLoadingAnything(): void
    {
        $emptyOptions = new ProductFilterOptions();
        $batchLoadData = [
            new ProductFilterOptionsBatchLoadData($this->createStub(Category::class), '', new ProductFilterData()),
            new ProductFilterOptionsBatchLoadData(null, 'phone', new ProductFilterData()),
        ];

        $productFilterFacade = $this->createMock(ProductFilterFacade::class);
        $productFilterFacade->expects($this->never())->method('getProductFilterConfigsByBatchLoadData');

        $productOnCurrentDomainElasticFacade = $this->createMock(ProductOnCurrentDomainElasticFacade::class);
        $productOnCurrentDomainElasticFacade->expects($this->never())->method('getProductFilterCountDataByBatchLoadData');

        $productFilterOptionsFactory = $this->createStub(ProductFilterOptionsFactory::class);
        $productFilterOptionsFactory->method('createProductFilterOptionsInstance')->willReturn($emptyOptions);

        $loader = $this->createLoader(false, $productFilterFacade, $productOnCurrentDomainElasticFacade, $this->createStub(CategoryParameterFacade::class), $productFilterOptionsFactory);

        $this->assertSame([$emptyOptions, $emptyOptions], $this->promiseAdapter->wait($loader->loadByBatchLoadData($batchLoadData)));
    }

    public function testOptionsAreBuiltFromOneBatchOfConfigsAndCountDataInInputOrder(): void
    {
        $category = $this->createStub(Category::class);
        $category->method('getId')->willReturn(self::CATEGORY_ID);
        $categoryBatchLoadData = new ProductFilterOptionsBatchLoadData($category, '', new ProductFilterData());
        $brandBatchLoadData = new ProductFilterOptionsBatchLoadData($this->createStub(Brand::class), '', new ProductFilterData());
        $searchBatchLoadData = new ProductFilterOptionsBatchLoadData(null, 'phone', new ProductFilterData());
        $batchLoadData = [$categoryBatchLoadData, $brandBatchLoadData, $searchBatchLoadData];

        $categoryConfig = $this->createStub(ProductFilterConfig::class);
        $brandConfig = $this->createStub(ProductFilterConfig::class);
        $searchConfig = $this->createStub(ProductFilterConfig::class);
        $categoryCountData = new ProductFilterCountData();
        $brandCountData = new ProductFilterCountData();
        $searchCountData = new ProductFilterCountData();
        $collapsedParameter = $this->createStub(Parameter::class);
        $categoryOptions = new ProductFilterOptions();
        $brandOptions = new ProductFilterOptions();
        $searchOptions = new ProductFilterOptions();

        $productFilterFacade = $this->createMock(ProductFilterFacade::class);
        $productFilterFacade->expects($this->once())
            ->method('getProductFilterConfigsByBatchLoadData')
            ->with($batchLoadData)
            ->willReturn([$categoryConfig, $brandConfig, $searchConfig]);

        $productOnCurrentDomainElasticFacade = $this->createMock(ProductOnCurrentDomainElasticFacade::class);
        $productOnCurrentDomainElasticFacade->expects($this->once())
            ->method('getProductFilterCountDataByBatchLoadData')
            ->with($batchLoadData)
            ->willReturn([$categoryCountData, $brandCountData, $searchCountData]);

        $categoryParameterFacade = $this->createMock(CategoryParameterFacade::class);
        $categoryParameterFacade->expects($this->once())
            ->method('getParametersCollapsedIndexedByCategoryId')
            ->with([$category])
            ->willReturn([self::CATEGORY_ID => [$collapsedParameter]]);

        $productFilterOptionsFactory = $this->createMock(ProductFilterOptionsFactory::class);
        $productFilterOptionsFactory->expects($this->exactly(3))
            ->method('createProductFilterOptionsByBatchLoadData')
            ->willReturnMap([
                [$categoryBatchLoadData, $categoryConfig, $categoryCountData, [$collapsedParameter], $categoryOptions],
                [$brandBatchLoadData, $brandConfig, $brandCountData, [], $brandOptions],
                [$searchBatchLoadData, $searchConfig, $searchCountData, [], $searchOptions],
            ]);

        $loader = $this->createLoader(true, $productFilterFacade, $productOnCurrentDomainElasticFacade, $categoryParameterFacade, $productFilterOptionsFactory);

        $this->assertSame(
            [$categoryOptions, $brandOptions, $searchOptions],
            $this->promiseAdapter->wait($loader->loadByBatchLoadData($batchLoadData)),
        );
    }

    public function testCollapsedParametersAreNotLoadedWithoutCategoryInBatch(): void
    {
        $batchLoadData = [new ProductFilterOptionsBatchLoadData($this->createStub(Brand::class), '', new ProductFilterData())];
        $brandOptions = new ProductFilterOptions();

        $productFilterFacade = $this->createStub(ProductFilterFacade::class);
        $productFilterFacade->method('getProductFilterConfigsByBatchLoadData')->willReturn([$this->createStub(ProductFilterConfig::class)]);

        $productOnCurrentDomainElasticFacade = $this->createStub(ProductOnCurrentDomainElasticFacade::class);
        $productOnCurrentDomainElasticFacade->method('getProductFilterCountDataByBatchLoadData')->willReturn([new ProductFilterCountData()]);

        $categoryParameterFacade = $this->createMock(CategoryParameterFacade::class);
        $categoryParameterFacade->expects($this->never())->method('getParametersCollapsedIndexedByCategoryId');

        $productFilterOptionsFactory = $this->createStub(ProductFilterOptionsFactory::class);
        $productFilterOptionsFactory->method('createProductFilterOptionsByBatchLoadData')->willReturn($brandOptions);

        $loader = $this->createLoader(true, $productFilterFacade, $productOnCurrentDomainElasticFacade, $categoryParameterFacade, $productFilterOptionsFactory);

        $this->assertSame([$brandOptions], $this->promiseAdapter->wait($loader->loadByBatchLoadData($batchLoadData)));
    }

    private function createLoader(
        bool $isProductFilterCountsEnabled,
        ProductFilterFacade $productFilterFacade,
        ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade,
        CategoryParameterFacade $categoryParameterFacade,
        ProductFilterOptionsFactory $productFilterOptionsFactory,
    ): ProductFilterOptionsBatchLoader {
        $moduleFacade = $this->createStub(ModuleFacade::class);
        $moduleFacade->method('isEnabled')->willReturnMap([[ModuleList::PRODUCT_FILTER_COUNTS, $isProductFilterCountsEnabled]]);

        return new ProductFilterOptionsBatchLoader(
            $this->promiseAdapter,
            $moduleFacade,
            $productFilterFacade,
            $productOnCurrentDomainElasticFacade,
            $categoryParameterFacade,
            $productFilterOptionsFactory,
        );
    }
}
