<?php

declare(strict_types=1);

namespace Tests\LuigisBoxBundle\Unit\Model\Batch;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Adapter\SyncPromiseQueue;
use GraphQL\Executor\Promise\Promise;
use LogicException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductsResult;
use Shopsys\FrontendApiBundle\Model\Category\CategoryFacade;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoader;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadResult;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class LuigisBoxBatchLoaderTest extends TestCase
{
    private const string USER_IDENTIFIER = '123e4567-e89b-12d3-a456-426614174000';

    private SyncPromiseAdapter $promiseAdapter;

    private LuigisBoxClient|MockObject $luigisBoxClient;

    private ProductElasticsearchRepository|MockObject $productElasticsearchRepository;

    private FilterQueryFactory|MockObject $filterQueryFactory;

    private CategoryFacade|MockObject $categoryFacade;

    private Domain|MockObject $domain;

    private CombinedArticleElasticsearchFacade|MockObject $combinedArticleElasticsearchFacade;

    private BrandFacade|MockObject $brandFacade;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->promiseAdapter = new SyncPromiseAdapter();
        $this->luigisBoxClient = $this->createMock(LuigisBoxClient::class);
        $this->productElasticsearchRepository = $this->createMock(ProductElasticsearchRepository::class);
        $this->filterQueryFactory = $this->createMock(FilterQueryFactory::class);
        $this->categoryFacade = $this->createMock(CategoryFacade::class);
        $this->domain = $this->createMock(Domain::class);
        $this->combinedArticleElasticsearchFacade = $this->createMock(CombinedArticleElasticsearchFacade::class);
        $this->brandFacade = $this->createMock(BrandFacade::class);
    }

    public function testSearchBatchLoadsEachTypeBySeparateLuigisBoxRequest(): void
    {
        $facets = [['name' => 'brand']];
        $productHit = ['id' => 11, 'name' => 'Product'];
        $category = ['id' => 21, 'name' => 'Category'];
        $article = ['id' => 31, 'name' => 'Article'];
        $brand = ['id' => 41, 'name' => 'Brand'];
        $calledTypes = [];

        $filterQuery = $this->createStub(FilterQuery::class);
        $this->mockLuigisBoxClientSearchResults($calledTypes, $facets);
        $this->filterQueryFactory->expects($this->once())->method('createSellableProductsByProductIdsFilter')
            ->with([11], 5)
            ->willReturn($filterQuery);
        $this->productElasticsearchRepository->expects($this->once())->method('getSortedProductsResultByFilterQuery')
            ->with($filterQuery)
            ->willReturn(new ProductsResult(1, [$productHit]));
        $this->domain->expects($this->once())->method('getCurrentDomainConfig')
            ->willReturn($this->createStub(DomainConfig::class));
        $this->domain->expects($this->once())->method('getId')->willReturn(1);
        $this->categoryFacade->expects($this->once())->method('getVisibleCategoriesByIds')
            ->with([[21]], self::anything())
            ->willReturn([[$category]]);
        $this->combinedArticleElasticsearchFacade->expects($this->once())->method('getArticlesByIds')
            ->with(['article' => ['31']], 1, 1)
            ->willReturn([$article]);
        $this->brandFacade->expects($this->once())->method('getBrandsByIds')->with([41])->willReturn([$brand]);
        $loader = $this->createLoader();

        $promise = $loader->loadByBatchData([
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::PRODUCT, 5),
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::CATEGORY, 10),
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::ARTICLE, 50),
            $this->createSearchBatchLoadData(TypeInLuigisBoxEnum::BRAND, 50),
        ]);

        $result = $this->resolvePromise($promise);

        self::assertSame([
            TypeInLuigisBoxEnum::PRODUCT,
            TypeInLuigisBoxEnum::CATEGORY,
            TypeInLuigisBoxEnum::ARTICLE,
            TypeInLuigisBoxEnum::BRAND,
        ], $calledTypes);
        $this->assertBatchLoadResult($result[0], [$productHit], 1, $facets);
        $this->assertBatchLoadResult($result[1], [$category], 7);
        $this->assertBatchLoadResult($result[2], [$article], 3);
        $this->assertBatchLoadResult($result[3], [$brand], 2);
    }

    public function testBatchWithMixedAutocompleteAndSearchEndpointsKeepsResultsSeparate(): void
    {
        $brand = ['id' => 41, 'name' => 'Brand'];
        $category = ['id' => 21, 'name' => 'Category'];
        $autocompleteBatchLoadData = $this->createSearchBatchLoadData(
            TypeInLuigisBoxEnum::BRAND,
            50,
            LuigisBoxEndpointEnum::AUTOCOMPLETE,
            'sony',
        );
        $searchBatchLoadData = $this->createSearchBatchLoadData(
            TypeInLuigisBoxEnum::CATEGORY,
            10,
            LuigisBoxEndpointEnum::SEARCH,
            'notebook',
        );
        $this->luigisBoxClient->expects($this->once())
            ->method('getData')
            ->with($autocompleteBatchLoadData, [TypeInLuigisBoxEnum::BRAND => 50])
            ->willReturn([
                TypeInLuigisBoxEnum::BRAND => new LuigisBoxResult([41], ['brand-41'], 1, []),
            ]);
        $this->luigisBoxClient->expects($this->once())
            ->method('getDataForMultiple')
            ->with([1 => $searchBatchLoadData])
            ->willReturn([
                1 => [
                    TypeInLuigisBoxEnum::CATEGORY => new LuigisBoxResult([21], ['category-21'], 7, []),
                ],
            ]);
        $this->brandFacade->expects($this->once())->method('getBrandsByIds')->with([41])->willReturn([$brand]);
        $this->domain->expects($this->once())->method('getCurrentDomainConfig')
            ->willReturn($this->createStub(DomainConfig::class));
        $this->categoryFacade->expects($this->once())->method('getVisibleCategoriesByIds')
            ->with([[21]], self::anything())
            ->willReturn([[$category]]);
        $this->filterQueryFactory->expects($this->never())->method('createSellableProductsByProductIdsFilter');
        $this->productElasticsearchRepository->expects($this->never())->method('getSortedProductsResultByFilterQuery');
        $this->combinedArticleElasticsearchFacade->expects($this->never())->method('getArticlesByIds');
        $loader = $this->createLoader();

        $promise = $loader->loadByBatchData([
            $autocompleteBatchLoadData,
            $searchBatchLoadData,
        ]);

        $result = $this->resolvePromise($promise);

        $this->assertBatchLoadResult($result[0], [$brand], 1);
        $this->assertBatchLoadResult($result[1], [$category], 7);
    }

    public function testBatchWithAutocompleteAndSearchForSameTypeReturnsOneResultPerBatchItem(): void
    {
        $autocompleteProductHit = ['id' => 11, 'name' => 'Autocomplete product'];
        $searchProductHit = ['id' => 12, 'name' => 'Search product'];
        $autocompleteBatchLoadData = $this->createSearchBatchLoadData(
            TypeInLuigisBoxEnum::PRODUCT,
            5,
            LuigisBoxEndpointEnum::AUTOCOMPLETE,
            'sony',
        );
        $searchBatchLoadData = $this->createSearchBatchLoadData(
            TypeInLuigisBoxEnum::PRODUCT,
            10,
            LuigisBoxEndpointEnum::SEARCH,
            'notebook',
        );
        $autocompleteFilterQuery = $this->createStub(FilterQuery::class);
        $searchFilterQuery = $this->createStub(FilterQuery::class);
        $this->luigisBoxClient->expects($this->once())
            ->method('getData')
            ->with($autocompleteBatchLoadData, [TypeInLuigisBoxEnum::PRODUCT => 5])
            ->willReturn([
                TypeInLuigisBoxEnum::PRODUCT => new LuigisBoxResult([11], ['product-11'], 1, []),
            ]);
        $this->luigisBoxClient->expects($this->once())
            ->method('getDataForMultiple')
            ->with([1 => $searchBatchLoadData])
            ->willReturn([
                1 => [
                    TypeInLuigisBoxEnum::PRODUCT => new LuigisBoxResult([12], ['product-12'], 3, []),
                ],
            ]);
        $this->filterQueryFactory->expects($this->exactly(2))
            ->method('createSellableProductsByProductIdsFilter')
            ->willReturnCallback(function (array $productIds, int $limit) use ($autocompleteFilterQuery, $searchFilterQuery): FilterQuery {
                if ($productIds === [11] && $limit === 5) {
                    return $autocompleteFilterQuery;
                }

                if ($productIds === [12] && $limit === 10) {
                    return $searchFilterQuery;
                }

                throw new LogicException('Unexpected product filter query arguments.');
            });
        $this->productElasticsearchRepository->expects($this->exactly(2))
            ->method('getSortedProductsResultByFilterQuery')
            ->willReturnCallback(
                function (FilterQuery $filterQuery) use (
                    $autocompleteFilterQuery,
                    $searchFilterQuery,
                    $autocompleteProductHit,
                    $searchProductHit,
                ): ProductsResult {
                    if ($filterQuery === $autocompleteFilterQuery) {
                        return new ProductsResult(1, [$autocompleteProductHit]);
                    }

                    if ($filterQuery === $searchFilterQuery) {
                        return new ProductsResult(1, [$searchProductHit]);
                    }

                    throw new LogicException('Unexpected filter query.');
                },
            );
        $this->categoryFacade->expects($this->never())->method('getVisibleCategoriesByIds');
        $this->domain->expects($this->never())->method('getCurrentDomainConfig');
        $this->domain->expects($this->never())->method('getId');
        $this->combinedArticleElasticsearchFacade->expects($this->never())->method('getArticlesByIds');
        $this->brandFacade->expects($this->never())->method('getBrandsByIds');
        $loader = $this->createLoader();

        $promise = $loader->loadByBatchData([
            $autocompleteBatchLoadData,
            $searchBatchLoadData,
        ]);

        $result = $this->resolvePromise($promise);

        $this->assertBatchLoadResult($result[0], [$autocompleteProductHit], 1);
        $this->assertBatchLoadResult($result[1], [$searchProductHit], 3);
    }

    /**
     * @param string[] $calledTypes
     * @param array<int, array<string, mixed>> $facets
     */
    private function mockLuigisBoxClientSearchResults(array &$calledTypes, array $facets): void
    {
        $this->luigisBoxClient->expects($this->once())
            ->method('getDataForMultiple')
            ->willReturnCallback(
                function (array $batchLoadDataItems) use (&$calledTypes, $facets): array {
                    $resultsByKey = [];

                    foreach ($batchLoadDataItems as $key => $batchLoadData) {
                        $type = $batchLoadData->getType();
                        $calledTypes[] = $type;

                        $resultsByKey[$key] = [$type => match ($type) {
                            TypeInLuigisBoxEnum::PRODUCT => new LuigisBoxResult([11], ['product-11'], 1, $facets),
                            TypeInLuigisBoxEnum::CATEGORY => new LuigisBoxResult([21], ['category-21'], 7, []),
                            TypeInLuigisBoxEnum::ARTICLE => new LuigisBoxResult([31], ['article-31'], 3, []),
                            TypeInLuigisBoxEnum::BRAND => new LuigisBoxResult([41], ['brand-41'], 2, []),
                            default => throw new LogicException(sprintf('Unexpected Luigi\'s Box type "%s".', $type)),
                        }];
                    }

                    return $resultsByKey;
                },
            );
    }

    /**
     * @param array<int, mixed> $expectedData
     * @param array<int, array<string, mixed>> $expectedFacets
     */
    private function assertBatchLoadResult(
        mixed $result,
        array $expectedData,
        int $expectedTotalCount,
        array $expectedFacets = [],
    ): void {
        self::assertInstanceOf(LuigisBoxBatchLoadResult::class, $result);
        self::assertSame($expectedData, $result->getData());
        self::assertSame($expectedTotalCount, $result->getTotalCount());
        self::assertSame($expectedFacets, $result->getFacets());
    }

    private function createLoader(): LuigisBoxBatchLoader
    {
        return new LuigisBoxBatchLoader(
            $this->luigisBoxClient,
            $this->promiseAdapter,
            $this->productElasticsearchRepository,
            $this->filterQueryFactory,
            $this->categoryFacade,
            $this->domain,
            $this->combinedArticleElasticsearchFacade,
            $this->brandFacade,
        );
    }

    private function createSearchBatchLoadData(
        string $type,
        int $limit,
        string $endpoint = LuigisBoxEndpointEnum::SEARCH,
        string $query = 'shipping',
    ): LuigisBoxSearchBatchLoadData {
        return new LuigisBoxSearchBatchLoadData(
            $type,
            $endpoint,
            self::USER_IDENTIFIER,
            $limit,
            $query,
            0,
            ['f' => ['type:' . $type]],
        );
    }

    private function resolvePromise(Promise $promise): mixed
    {
        $resolved = null;

        $promise->then(static function ($value) use (&$resolved): void {
            $resolved = $value;
        });

        SyncPromiseQueue::run();

        return $resolved;
    }
}
