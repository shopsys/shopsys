<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Search\AggregationResultToProductFilterCountDataTransformer;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataRequest;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer;

class ProductFilterCountDataElasticsearchRepositoryTest extends TestCase
{
    private const int FLAG_ID = 7;

    private const int BRAND_ID = 5;

    private const int PARAMETER_ID = 3;

    private const int PARAMETER_VALUE_ID = 30;

    private const string INDEX_NAME = 'product_1';

    public function testCountDataOfAllRequestsIsLoadedWithOneMultiSearch(): void
    {
        $requests = [
            'category' => new ProductFilterCountDataRequest($this->createProductFilterDataWithFlagAndParameterFilter(), $this->createFilterQuery()->filterByCategory(11), true),
            'brand' => new ProductFilterCountDataRequest($this->createProductFilterDataWithBrandFilter(), $this->createFilterQuery()->filterByBrands([9]), true),
            'search' => new ProductFilterCountDataRequest(new ProductFilterData(), $this->createFilterQuery(), false),
        ];

        $multipleSearchFacade = $this->createMock(MultipleSearchFacade::class);
        $multipleSearchFacade->expects($this->once())
            ->method('searchIndexedByKey')
            ->with($this->callback($this->assertSearchQueriesOfAllRequests(...)))
            ->willReturn([
                'category|absolute_numbers' => $this->createAbsoluteNumbersResponse(
                    [self::FLAG_ID => 2, 9 => 1],
                    [5 => 3],
                    4,
                    [self::PARAMETER_ID => [self::PARAMETER_VALUE_ID => 1, 31 => 2]],
                ),
                'category|flags_plus_numbers' => $this->createAbsoluteNumbersResponse([9 => 6], [], 0),
                'category|parameter_plus_numbers_3' => $this->createParameterPlusNumbersResponse([31 => 8]),
                'brand|absolute_numbers' => $this->createAbsoluteNumbersResponse([9 => 3], [self::BRAND_ID => 2, 6 => 1], 2),
                'brand|brands_plus_numbers' => $this->createAbsoluteNumbersResponse([], [6 => 4], 0),
                'search|absolute_numbers' => $this->createAbsoluteNumbersResponse([9 => 5], [5 => 1, 6 => 2], 7),
            ]);

        $repository = new ProductFilterCountDataElasticsearchRepository(
            $multipleSearchFacade,
            new ProductFilterDataToQueryTransformer(),
            new AggregationResultToProductFilterCountDataTransformer(),
        );

        $countDataIndexedByKey = $repository->getProductFilterCountDataByRequests($requests);

        $this->assertSame(['category', 'brand', 'search'], array_keys($countDataIndexedByKey));

        $categoryCountData = $countDataIndexedByKey['category'];
        $this->assertSame(4, $categoryCountData->countInStock);
        $this->assertSame([9 => 6], $categoryCountData->countByFlagId);
        $this->assertSame([5 => 3], $categoryCountData->countByBrandId);
        $this->assertSame([self::PARAMETER_ID => [31 => 8]], $categoryCountData->countByParameterIdAndValueId);

        $brandCountData = $countDataIndexedByKey['brand'];
        $this->assertSame(2, $brandCountData->countInStock);
        $this->assertSame([9 => 3], $brandCountData->countByFlagId);
        $this->assertSame([6 => 4], $brandCountData->countByBrandId);
        $this->assertSame([], $brandCountData->countByParameterIdAndValueId);

        $searchCountData = $countDataIndexedByKey['search'];
        $this->assertSame(7, $searchCountData->countInStock);
        $this->assertSame([9 => 5], $searchCountData->countByFlagId);
        $this->assertSame([5 => 1, 6 => 2], $searchCountData->countByBrandId);
        $this->assertSame([], $searchCountData->countByParameterIdAndValueId);
    }

    /**
     * @param array<string, array{index: string, body: array<string, mixed>}> $searchQueriesIndexedByKey
     */
    private function assertSearchQueriesOfAllRequests(array $searchQueriesIndexedByKey): bool
    {
        $this->assertSame(
            [
                'category|absolute_numbers',
                'category|flags_plus_numbers',
                'category|parameter_plus_numbers_3',
                'brand|absolute_numbers',
                'brand|brands_plus_numbers',
                'search|absolute_numbers',
            ],
            array_keys($searchQueriesIndexedByKey),
        );

        $categoryAbsoluteNumbersBody = $searchQueriesIndexedByKey['category|absolute_numbers']['body'];
        $this->assertSame(self::INDEX_NAME, $searchQueriesIndexedByKey['category|absolute_numbers']['index']);
        $this->assertArrayHasKey('parameters', $categoryAbsoluteNumbersBody['aggs']);
        $this->assertContains(['terms' => ['flags' => [self::FLAG_ID]]], $categoryAbsoluteNumbersBody['query']['bool']['filter']);

        $categoryFlagsPlusNumbersBody = $searchQueriesIndexedByKey['category|flags_plus_numbers']['body'];
        $this->assertSame(['terms' => ['flags' => [self::FLAG_ID]]], $categoryFlagsPlusNumbersBody['query']['bool']['must_not']);
        $this->assertNotContains(['terms' => ['flags' => [self::FLAG_ID]]], $categoryFlagsPlusNumbersBody['query']['bool']['filter']);

        $categoryParameterPlusNumbersBody = $searchQueriesIndexedByKey['category|parameter_plus_numbers_3']['body'];
        $this->assertSame(
            self::PARAMETER_ID,
            $categoryParameterPlusNumbersBody['aggs']['parameters']['aggs']['filtered_for_parameter']['filter']['term']['parameters.parameter_id'],
        );

        $brandAbsoluteNumbersBody = $searchQueriesIndexedByKey['brand|absolute_numbers']['body'];
        $this->assertContains(['terms' => ['brand' => [self::BRAND_ID]]], $brandAbsoluteNumbersBody['query']['bool']['filter']);

        $brandBrandsPlusNumbersBody = $searchQueriesIndexedByKey['brand|brands_plus_numbers']['body'];
        $this->assertSame(['terms' => ['brand' => [self::BRAND_ID]]], $brandBrandsPlusNumbersBody['query']['bool']['must_not']);
        $this->assertNotContains(['terms' => ['brand' => [self::BRAND_ID]]], $brandBrandsPlusNumbersBody['query']['bool']['filter']);

        $searchAbsoluteNumbersBody = $searchQueriesIndexedByKey['search|absolute_numbers']['body'];
        $this->assertArrayNotHasKey('parameters', $searchAbsoluteNumbersBody['aggs']);

        return true;
    }

    private function createProductFilterDataWithFlagAndParameterFilter(): ProductFilterData
    {
        $flag = $this->createStub(Flag::class);
        $flag->method('getId')->willReturn(self::FLAG_ID);

        $parameter = $this->createStub(Parameter::class);
        $parameter->method('getId')->willReturn(self::PARAMETER_ID);
        $parameter->method('isSlider')->willReturn(false);

        $parameterValue = $this->createStub(ParameterValue::class);
        $parameterValue->method('getId')->willReturn(self::PARAMETER_VALUE_ID);

        $parameterFilterData = new ParameterFilterData();
        $parameterFilterData->parameter = $parameter;
        $parameterFilterData->values = [$parameterValue];

        $productFilterData = new ProductFilterData();
        $productFilterData->flags = [$flag];
        $productFilterData->parameters = [$parameterFilterData];

        return $productFilterData;
    }

    private function createProductFilterDataWithBrandFilter(): ProductFilterData
    {
        $brand = $this->createStub(Brand::class);
        $brand->method('getId')->willReturn(self::BRAND_ID);

        $productFilterData = new ProductFilterData();
        $productFilterData->brands = [$brand];

        return $productFilterData;
    }

    private function createFilterQuery(): FilterQuery
    {
        return new FilterQuery(self::INDEX_NAME, PricingSetting::PRICE_TYPE_WITH_VAT);
    }

    /**
     * @param array<int, int> $countByFlagId
     * @param array<int, int> $countByBrandId
     * @param array<int, array<int, int>> $countByParameterIdAndValueId
     * @return array<string, mixed>
     */
    private function createAbsoluteNumbersResponse(
        array $countByFlagId,
        array $countByBrandId,
        int $countInStock,
        array $countByParameterIdAndValueId = [],
    ): array {
        $parameterBuckets = [];

        foreach ($countByParameterIdAndValueId as $parameterId => $countByValueId) {
            $parameterBuckets[] = [
                'key' => $parameterId,
                'by_value' => ['buckets' => $this->createBuckets($countByValueId)],
            ];
        }

        return [
            'aggregations' => [
                'flags' => ['buckets' => $this->createBuckets($countByFlagId)],
                'brands' => ['buckets' => $this->createBuckets($countByBrandId)],
                'stock' => ['doc_count' => $countInStock],
                'parameters' => ['by_parameters' => ['buckets' => $parameterBuckets]],
            ],
        ];
    }

    /**
     * @param array<int, int> $countByValueId
     * @return array<string, mixed>
     */
    private function createParameterPlusNumbersResponse(array $countByValueId): array
    {
        return [
            'aggregations' => [
                'parameters' => [
                    'filtered_for_parameter' => [
                        'by_parameters' => [
                            'buckets' => [
                                ['by_value' => ['buckets' => $this->createBuckets($countByValueId)]],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<int, int> $countByKey
     * @return array<int, array{key: int, doc_count: int}>
     */
    private function createBuckets(array $countByKey): array
    {
        $buckets = [];

        foreach ($countByKey as $key => $count) {
            $buckets[] = ['key' => $key, 'doc_count' => $count];
        }

        return $buckets;
    }
}
