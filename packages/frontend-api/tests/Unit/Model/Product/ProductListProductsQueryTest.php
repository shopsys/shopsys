<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Product;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\ArgumentFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductList\ProductListApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Flag\FlagQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsQuery;
use Webmozart\Assert\Assert;

class ProductListProductsQueryTest extends TestCase
{
    public function testOnlyTheRequestedVisibleProductIdsReachTheFullProductLoader(): void
    {
        $adapter = new SyncPromiseAdapter();
        $identities = $this->createStub(ProductListApiFacade::class);
        $identities->method('getVisibleProductIdentifiers')->willReturn($adapter->createFulfilled([
            ['id' => 33, 'uuid' => 'visible-first'],
            ['id' => 52, 'uuid' => 'visible-second'],
        ]));
        $products = $this->createMock(DataLoaderInterface::class);
        // The loader represents the expensive product-document read, so its requested IDs are observable I/O.
        $products->expects($this->once())->method('loadMany')->with([[52]])
            ->willReturn($adapter->createFulfilled([[['id' => 52]]]));
        $query = $this->createQuery($identities, $products);

        $result = $adapter->wait($query->productsByProductListQuery(
            $this->createStub(ProductList::class),
            $this->createArgument(['offset' => 1, 'limit' => 1]),
        ));

        $this->assertSame([['id' => 52]], $result);
    }

    public function testCountAndMembershipNeverLoadProductDocuments(): void
    {
        $adapter = new SyncPromiseAdapter();
        $identities = $this->createStub(ProductListApiFacade::class);
        $identities->method('getVisibleProductIdentifiers')->willReturn($adapter->createFulfilled([
            ['id' => 33, 'uuid' => 'visible-first'],
            ['id' => 52, 'uuid' => 'visible-second'],
        ]));
        $products = $this->createMock(DataLoaderInterface::class);
        $products->expects($this->never())->method('load');
        $products->expects($this->never())->method('loadMany');
        $query = $this->createQuery($identities, $products);
        $list = $this->createStub(ProductList::class);

        $count = $adapter->wait($query->productsCountByProductListQuery($list));
        $uuids = $adapter->wait($query->productUuidsByProductListQuery($list));

        $this->assertSame(2, $count);
        $this->assertSame(['visible-first', 'visible-second'], $uuids);
    }

    public function testLargeRequestedRangeIsNotTruncatedByTheProductLoaderWindow(): void
    {
        $adapter = new SyncPromiseAdapter();
        $identities = $this->createStub(ProductListApiFacade::class);
        $identities->method('getVisibleProductIdentifiers')->willReturn($adapter->createFulfilled(array_map(
            static fn (int $id): array => ['id' => $id, 'uuid' => 'uuid-' . $id],
            range(1, 1001),
        )));
        $products = $this->createMock(DataLoaderInterface::class);
        $products->expects($this->once())->method('loadMany')->with([range(1, 1000), [1001]])->willReturnCallback(
            static fn (array $batches) => $adapter->createFulfilled(array_map(
                static fn (array $ids): array => array_map(static fn (int $id): array => ['id' => $id], $ids),
                $batches,
            )),
        );
        $query = $this->createQuery($identities, $products);

        $result = $adapter->wait($query->productsByProductListQuery(
            $this->createStub(ProductList::class),
            $this->createArgument(['offset' => 0, 'limit' => 1001]),
        ));

        $this->assertCount(1001, $result);
        $this->assertSame(['id' => 1001], $result[1000]);
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function invalidRanges(): iterable
    {
        yield 'negative offset' => [-1, 1];

        yield 'negative limit' => [0, -1];
    }

    #[DataProvider('invalidRanges')]
    public function testNegativeRangesAreRejected(int $offset, int $limit): void
    {
        $query = $this->createQuery($this->createStub(ProductListApiFacade::class), $this->createStub(DataLoaderInterface::class));

        $this->expectException(InvalidArgumentUserError::class);
        $query->productsByProductListQuery($this->createStub(ProductList::class), $this->createArgument(['offset' => $offset, 'limit' => $limit]));
    }

    /**
     * @param array<string, int> $values
     */
    private function createArgument(array $values): Argument
    {
        $argument = new ArgumentFactory(Argument::class)->create($values);
        Assert::isInstanceOf($argument, Argument::class);

        return $argument;
    }

    private function createQuery(
        ProductListApiFacade $identities,
        DataLoaderInterface $products,
    ): ProductsQuery {
        return new ProductsQuery(
            $this->createStub(ProductFacade::class),
            $this->createStub(ProductFilterFacade::class),
            $this->createStub(ProductConnectionFactory::class),
            $products,
            $identities,
            $this->createStub(ProductRepository::class),
            $this->createStub(ProductOrderingModeProvider::class),
            $this->createStub(ProductFilterDataFactory::class),
            $this->createStub(DataLoaderInterface::class),
            $this->createStub(BrandQuery::class),
            $this->createStub(CategoryQuery::class),
            $this->createStub(FlagQuery::class),
        );
    }
}
