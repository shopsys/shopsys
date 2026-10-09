<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountDataBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;

class ProductOnCurrentDomainElasticFacadeTest extends TestCase
{
    private const int BRAND_ID = 5;

    private const int FLAG_ID = 7;

    public function testEveryListingKindGetsItsOwnBaseFilterQueryAndParametersOnlyForEntityListings(): void
    {
        $category = $this->createStub(Category::class);
        $brand = $this->createStub(Brand::class);
        $brand->method('getId')->willReturn(self::BRAND_ID);
        $flag = $this->createStub(Flag::class);
        $flag->method('getId')->willReturn(self::FLAG_ID);
        $productFilterData = new ProductFilterData();

        $categoryFilterQuery = $this->createFilterQuery('category');
        $brandFilterQuery = $this->createFilterQuery('brand');
        $flagFilterQuery = $this->createFilterQuery('flag');
        $searchFilterQuery = $this->createFilterQuery('search');
        $allFilterQuery = $this->createFilterQuery('all');

        $filterQueryFactory = $this->createMock(FilterQueryFactory::class);
        $filterQueryFactory->expects($this->once())->method('createListableProductsByCategoryWithPriceAndStockFilter')->with($category, $productFilterData)->willReturn($categoryFilterQuery);
        $filterQueryFactory->expects($this->once())->method('createListableProductsByBrandIdWithPriceAndStockFilter')->with(self::BRAND_ID, $productFilterData)->willReturn($brandFilterQuery);
        $filterQueryFactory->expects($this->once())->method('createListableProductsByFlagIdWithPriceAndStockFilter')->with(self::FLAG_ID, $productFilterData)->willReturn($flagFilterQuery);
        $filterQueryFactory->expects($this->once())->method('createListableProductsBySearchTextWithPriceAndStockFilter')->with('phone', $productFilterData)->willReturn($searchFilterQuery);
        $filterQueryFactory->expects($this->once())->method('createListableProductsWithPriceAndStockFilter')->with($productFilterData)->willReturn($allFilterQuery);

        $expectedCountData = [
            'category' => new ProductFilterCountData(),
            'brand' => new ProductFilterCountData(),
            'flag' => new ProductFilterCountData(),
            'search' => new ProductFilterCountData(),
            'all' => new ProductFilterCountData(),
        ];

        $productFilterCountDataElasticsearchRepository = $this->createMock(ProductFilterCountDataElasticsearchRepository::class);
        $productFilterCountDataElasticsearchRepository->expects($this->once())
            ->method('getProductFilterCountDataByRequests')
            ->with($this->callback(function (array $requestsIndexedByKey) use ($categoryFilterQuery, $brandFilterQuery, $flagFilterQuery, $searchFilterQuery, $allFilterQuery, $productFilterData): bool {
                $this->assertSame(['category', 'brand', 'flag', 'search', 'all'], array_keys($requestsIndexedByKey));

                foreach ($requestsIndexedByKey as $request) {
                    $this->assertSame($productFilterData, $request->getProductFilterData());
                }

                $this->assertSame($categoryFilterQuery, $requestsIndexedByKey['category']->getBaseFilterQuery());
                $this->assertTrue($requestsIndexedByKey['category']->isWithParameters());
                $this->assertSame($brandFilterQuery, $requestsIndexedByKey['brand']->getBaseFilterQuery());
                $this->assertTrue($requestsIndexedByKey['brand']->isWithParameters());
                $this->assertSame($flagFilterQuery, $requestsIndexedByKey['flag']->getBaseFilterQuery());
                $this->assertTrue($requestsIndexedByKey['flag']->isWithParameters());
                $this->assertSame($searchFilterQuery, $requestsIndexedByKey['search']->getBaseFilterQuery());
                $this->assertFalse($requestsIndexedByKey['search']->isWithParameters());
                $this->assertSame($allFilterQuery, $requestsIndexedByKey['all']->getBaseFilterQuery());
                $this->assertFalse($requestsIndexedByKey['all']->isWithParameters());

                return true;
            }))
            ->willReturn($expectedCountData);

        $productOnCurrentDomainElasticFacade = new ProductOnCurrentDomainElasticFacade(
            $this->createStub(ProductElasticsearchRepository::class),
            $productFilterCountDataElasticsearchRepository,
            $filterQueryFactory,
        );

        $countDataIndexedByKey = $productOnCurrentDomainElasticFacade->getProductFilterCountDataByBatchLoadData([
            'category' => new ProductFilterCountDataBatchLoadData($category, '', $productFilterData),
            'brand' => new ProductFilterCountDataBatchLoadData($brand, '', $productFilterData),
            'flag' => new ProductFilterCountDataBatchLoadData($flag, '', $productFilterData),
            'search' => new ProductFilterCountDataBatchLoadData(null, 'phone', $productFilterData),
            'all' => new ProductFilterCountDataBatchLoadData(null, '', $productFilterData),
        ]);

        $this->assertSame($expectedCountData, $countDataIndexedByKey);
    }

    private function createFilterQuery(string $indexName): FilterQuery
    {
        return new FilterQuery($indexName, PricingSetting::PRICE_TYPE_WITH_VAT);
    }
}
