<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\DataLoader\DataLoader;
use Overblog\PromiseAdapter\Adapter\WebonyxGraphQLSyncPromiseAdapter;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchRepository;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFactory;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListItemFactory;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer;
use Shopsys\FrontendApiBundle\Component\Elasticsearch\ElasticsearchSearchBatchLoader;
use Shopsys\FrontendApiBundle\Model\Product\ProductList\ProductListApiFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductList\ProductListInputValidationFactory;

class ProductListApiFacadeTest extends TestCase
{
    public function testReadsIdentifiersOnceForRepeatedConsumersAndPreservesVisibleListOrder(): void
    {
        $factory = $this->createStub(FilterQueryFactory::class);
        $factory->method('createVisible')->willReturn($this->createFilterQuery());
        $repository = $this->createMock(MultipleSearchRepository::class);
        // This boundary is the actual Elasticsearch request, whose payload size is part of the contract.
        $repository->expects($this->once())->method('searchIndexedByKey')->willReturnCallback(function (array $queries): array {
            $this->assertCount(1, $queries);
            $this->assertFalse($queries[0]['body']['_source']);
            $this->assertSame(['id', 'uuid'], $queries[0]['body']['fields']);
            $this->assertSame(3, $queries[0]['body']['size']);

            return [0 => ['hits' => ['hits' => [
                ['fields' => ['id' => [1], 'uuid' => ['first']]],
                ['fields' => ['id' => [2], 'uuid' => ['second']]],
            ]]]];
        });
        // The repository is the SQL boundary; field resolvers must share this read as well as Elasticsearch.
        $listRepository = $this->createMock(ProductListRepository::class);
        $listRepository->expects($this->once())->method('getProductIdsByProductList')->willReturn([2, 1, 3]);
        $lists = $this->createProductListFacade($listRepository, new InMemoryCache());
        $bridge = new WebonyxGraphQLSyncPromiseAdapter();
        $search = new ElasticsearchSearchBatchLoader($bridge->getWebonyxPromiseAdapter(), $repository);
        $loader = new ProductListApiFacade($lists, $this->createStub(CurrentCustomerUser::class), $this->createStub(ProductListInputValidationFactory::class), $factory, new DataLoader($search->loadByQueries(...), $bridge));

        $list = $this->createStub(ProductList::class);
        $list->method('getUuid')->willReturn('list-uuid');
        $first = $loader->getVisibleProductIdentifiers($list);
        $second = $loader->getVisibleProductIdentifiers($list);
        $third = $loader->getVisibleProductIdentifiers($list);
        $result = DataLoader::await($first);

        $this->assertSame($result, DataLoader::await($second));
        $this->assertSame($result, DataLoader::await($third));
        $this->assertSame([['id' => 2, 'uuid' => 'second'], ['id' => 1, 'uuid' => 'first']], $result);
    }

    public function testIdentityReadDoesNotTruncateAfterOneThousandProducts(): void
    {
        $factory = $this->createStub(FilterQueryFactory::class);
        $factory->method('createVisible')->willReturn($this->createFilterQuery());
        $repository = $this->createMock(MultipleSearchRepository::class);
        $repository->expects($this->once())->method('searchIndexedByKey')->willReturnCallback(function (array $queries): array {
            $this->assertSame(1000, $queries[0]['body']['size']);
            $this->assertSame(1, $queries[1]['body']['size']);
            $hits = array_map(static fn (int $id): array => ['fields' => ['id' => [$id], 'uuid' => ['uuid-' . $id]]], range(1, 1001));

            return [
                0 => ['hits' => ['hits' => array_slice($hits, 0, 1000)]],
                1 => ['hits' => ['hits' => array_slice($hits, 1000)]],
            ];
        });
        $lists = $this->createStub(ProductListFacade::class);
        $lists->method('getProductIdsByProductList')->willReturn(range(1, 1001));
        $bridge = new WebonyxGraphQLSyncPromiseAdapter();
        $search = new ElasticsearchSearchBatchLoader($bridge->getWebonyxPromiseAdapter(), $repository);
        $loader = new ProductListApiFacade($lists, $this->createStub(CurrentCustomerUser::class), $this->createStub(ProductListInputValidationFactory::class), $factory, new DataLoader($search->loadByQueries(...), $bridge));

        $result = DataLoader::await($loader->getVisibleProductIdentifiers($this->createStub(ProductList::class)));

        $this->assertCount(1001, $result);
        $this->assertSame('uuid-1001', $result[1000]['uuid']);
    }

    public function testListIdentifiersStayIsolatedAndEmptyResultsAreCachedUntilReset(): void
    {
        $cache = new InMemoryCache();
        $listRepository = $this->createMock(ProductListRepository::class);
        $listRepository->expects($this->exactly(3))->method('getProductIdsByProductList')->willReturnOnConsecutiveCalls([1], [], [2]);
        $factory = $this->createStub(FilterQueryFactory::class);
        $factory->method('createVisible')->willReturn($this->createFilterQuery());
        $searchRepository = $this->createMock(MultipleSearchRepository::class);
        $searchRepository->expects($this->exactly(2))->method('searchIndexedByKey')->willReturnOnConsecutiveCalls(
            [['hits' => ['hits' => [['fields' => ['id' => [1], 'uuid' => ['first']]]]]]],
            [['hits' => ['hits' => [['fields' => ['id' => [2], 'uuid' => ['second']]]]]]],
        );
        $bridge = new WebonyxGraphQLSyncPromiseAdapter();
        $search = new ElasticsearchSearchBatchLoader($bridge->getWebonyxPromiseAdapter(), $searchRepository);
        $loader = new ProductListApiFacade($this->createProductListFacade($listRepository, $cache), $this->createStub(CurrentCustomerUser::class), $this->createStub(ProductListInputValidationFactory::class), $factory, new DataLoader($search->loadByQueries(...), $bridge));
        $list = $this->createStub(ProductList::class);
        $list->method('getUuid')->willReturn('populated-list');
        $emptyList = $this->createStub(ProductList::class);
        $emptyList->method('getUuid')->willReturn('empty-list');

        $this->assertSame([['id' => 1, 'uuid' => 'first']], DataLoader::await($loader->getVisibleProductIdentifiers($list)));
        $this->assertSame([], DataLoader::await($loader->getVisibleProductIdentifiers($emptyList)));
        $this->assertSame([], DataLoader::await($loader->getVisibleProductIdentifiers($emptyList)));
        $this->assertSame([['id' => 1, 'uuid' => 'first']], DataLoader::await($loader->getVisibleProductIdentifiers($list)));
        $cache->reset();

        $this->assertSame([['id' => 2, 'uuid' => 'second']], DataLoader::await($loader->getVisibleProductIdentifiers($list)));
    }

    private function createProductListFacade(
        ProductListRepository $repository,
        InMemoryCache $cache,
    ): ProductListFacade {
        return new ProductListFacade(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(ProductListFactory::class),
            $repository,
            $this->createStub(ProductListItemFactory::class),
            $cache,
        );
    }

    private function createFilterQuery(): FilterQuery
    {
        $factory = new FilterQueryFactory(
            $this->createStub(ProductFilterDataToQueryTransformer::class),
            $this->createStub(CurrentCustomerUser::class),
            $this->createStub(IndexDefinitionLoader::class),
            $this->createStub(Domain::class),
            $this->createStub(CategoryAutomatedFilterFacade::class),
            $this->createStub(PricingSetting::class),
        );

        return $factory->create('products');
    }
}
