<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Filter;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;

class ProductFilterElasticFacadeTest extends TestCase
{
    private const string INDEX_NAME = 'product_1';

    private const int PRICING_GROUP_ID = 2;

    private const int CATEGORY_ID = 11;

    private const int BRAND_ID = 5;

    private const int FLAG_ID = 7;

    private const array LISTING_KEYS = ['category', 'brand', 'flag', 'search', 'all'];

    public function testAggregationsOfAllListingsAreLoadedWithOneMultiSearch(): void
    {
        $pricingGroup = $this->createStub(PricingGroup::class);
        $pricingGroup->method('getId')->willReturn(self::PRICING_GROUP_ID);
        $batchLoadData = $this->createBatchLoadDataOfAllListingKinds();
        $idsDataIndexedByKey = [];
        $idsDataFactoryMap = [];
        $responsesIndexedByKey = [];

        foreach (self::LISTING_KEYS as $key) {
            $aggregations = ['flags' => ['buckets' => [['key' => $key]]]];
            $idsDataIndexedByKey[$key] = $this->createStub(ProductFilterConfigIdsData::class);
            $idsDataFactoryMap[] = [$aggregations, $idsDataIndexedByKey[$key]];
            $responsesIndexedByKey[$key] = ['aggregations' => $aggregations];
        }

        $multipleSearchFacade = $this->createMock(MultipleSearchFacade::class);
        $multipleSearchFacade->expects($this->once())
            ->method('searchIndexedByKey')
            ->with($this->callback($this->assertAggregationQueriesOfAllListings(...)))
            ->willReturn($responsesIndexedByKey);

        $productFilterConfigIdsDataFactory = $this->createMock(ProductFilterConfigIdsDataFactory::class);
        $productFilterConfigIdsDataFactory->expects($this->exactly(count(self::LISTING_KEYS)))
            ->method('createFromElasticsearchAggregationResult')
            ->willReturnMap($idsDataFactoryMap);

        $productFilterElasticFacade = new ProductFilterElasticFacade(
            $multipleSearchFacade,
            $this->createFilterQueryFactoryStub(),
            $productFilterConfigIdsDataFactory,
        );

        $this->assertSame(
            $idsDataIndexedByKey,
            $productFilterElasticFacade->getProductFilterConfigIdsDataByBatchLoadData($batchLoadData, $pricingGroup),
        );
    }

    /**
     * @param array<string, array{index: string, body: array<string, mixed>}> $aggregationQueriesIndexedByKey
     */
    private function assertAggregationQueriesOfAllListings(array $aggregationQueriesIndexedByKey): bool
    {
        $this->assertSame(self::LISTING_KEYS, array_keys($aggregationQueriesIndexedByKey));

        foreach ($aggregationQueriesIndexedByKey as $aggregationQuery) {
            $this->assertSame(self::INDEX_NAME, $aggregationQuery['index']);
            $this->assertContains(['term' => ['selling_denied' => false]], $aggregationQuery['body']['query']['bool']['filter']);
            $this->assertArrayHasKey('prices', $aggregationQuery['body']['aggs']);
        }

        $this->assertContains(['term' => ['categories' => self::CATEGORY_ID]], $aggregationQueriesIndexedByKey['category']['body']['query']['bool']['filter']);
        $this->assertArrayHasKey('parameters', $aggregationQueriesIndexedByKey['category']['body']['aggs']);
        $this->assertContains(['terms' => ['brand' => [self::BRAND_ID]]], $aggregationQueriesIndexedByKey['brand']['body']['query']['bool']['filter']);
        $this->assertContains(['terms' => ['flags' => [self::FLAG_ID]]], $aggregationQueriesIndexedByKey['flag']['body']['query']['bool']['filter']);
        $this->assertSame('phone', $aggregationQueriesIndexedByKey['search']['body']['query']['bool']['must']['multi_match']['query']);

        foreach (['brand', 'flag', 'search', 'all'] as $keyWithoutParameterChoices) {
            $this->assertArrayNotHasKey('parameters', $aggregationQueriesIndexedByKey[$keyWithoutParameterChoices]['body']['aggs']);
        }

        return true;
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadData>
     */
    private function createBatchLoadDataOfAllListingKinds(): array
    {
        $brand = $this->createStub(Brand::class);
        $brand->method('getId')->willReturn(self::BRAND_ID);
        $flag = $this->createStub(Flag::class);
        $flag->method('getId')->willReturn(self::FLAG_ID);

        return [
            'category' => new ProductFilterBatchLoadData($this->createStub(Category::class), ''),
            'brand' => new ProductFilterBatchLoadData($brand, ''),
            'flag' => new ProductFilterBatchLoadData($flag, ''),
            'search' => new ProductFilterBatchLoadData(null, 'phone'),
            'all' => new ProductFilterBatchLoadData(null, ''),
        ];
    }

    private function createFilterQueryFactoryStub(): FilterQueryFactory
    {
        $filterQueryFactory = $this->createStub(FilterQueryFactory::class);
        $filterQueryFactory->method('createVisibleForCategory')->willReturn($this->createFilterQuery()->filterByCategory(self::CATEGORY_ID));
        $filterQueryFactory->method('createVisible')->willReturn($this->createFilterQuery());

        return $filterQueryFactory;
    }

    private function createFilterQuery(): FilterQuery
    {
        return new FilterQuery(self::INDEX_NAME, PricingSetting::PRICE_TYPE_WITH_VAT);
    }
}
