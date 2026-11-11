<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Product;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\ArgumentFactory;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrontendApiBundle\Component\Validation\Exception\MaxAllowedLimitUserError;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
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
    public function testConnectionLoadsOnlyTheRequestedPageAndPaginatorLookahead(): void
    {
        $adapter = new SyncPromiseAdapter();
        $identities = $this->createStub(ProductListApiFacade::class);
        $identities->method('getVisibleProductIdentifiers')->willReturn($adapter->createFulfilled([
            ['id' => 33, 'uuid' => 'visible-first'],
            ['id' => 52, 'uuid' => 'visible-second'],
        ]));
        $products = $this->createMock(DataLoaderInterface::class);
        // The loader represents the expensive product-document read, so its requested IDs are observable I/O.
        $products->expects($this->once())->method('load')->with([33, 52])
            ->willReturn($adapter->createFulfilled([['id' => 33], ['id' => 52]]));
        $query = $this->createQuery($identities, $products);

        $result = $adapter->wait($query->productsConnectionByProductListQuery(
            $this->createStub(ProductList::class),
            $this->createArgument(['first' => 1, 'after' => (new ConnectionBuilder())->offsetToCursor(0)]),
        ));

        $this->assertSame([52], array_map(static fn ($edge): int => $edge->getNode()['id'], $result->getEdges()));
        $this->assertSame(2, $result->getTotalCount());
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

    public function testLegacyProductsUseOneBoundedLoaderRequestWithoutReadingAllMetadata(): void
    {
        $adapter = new SyncPromiseAdapter();
        $identities = $this->createMock(ProductListApiFacade::class);
        $identities->expects($this->never())->method('getVisibleProductIdentifiers');
        $lists = $this->createStub(ProductListFacade::class);
        $lists->method('getProductIdsByProductList')->willReturn(range(1, 1001));
        $products = $this->createMock(DataLoaderInterface::class);
        // The existing loader caps one Elasticsearch result at 1000; splitting the IDs bypasses that bound.
        $products->expects($this->never())->method('loadMany');
        $products->expects($this->once())->method('load')->with(range(1, 1001))->willReturn(
            $adapter->createFulfilled(array_map(static fn (int $id): array => ['id' => $id], range(1, 1000))),
        );
        $query = $this->createQuery($identities, $products, $lists);

        $result = $adapter->wait($query->productsByProductListQuery($this->createStub(ProductList::class)));

        $this->assertCount(1000, $result);
        $this->assertSame(['id' => 1000], $result[999]);
    }

    public function testConnectionCanReadThePageAfterOneThousandProducts(): void
    {
        $adapter = new SyncPromiseAdapter();
        $identities = $this->createStub(ProductListApiFacade::class);
        $identities->method('getVisibleProductIdentifiers')->willReturn($adapter->createFulfilled(array_map(
            static fn (int $id): array => ['id' => $id, 'uuid' => 'uuid-' . $id],
            range(1, 1001),
        )));
        $products = $this->createMock(DataLoaderInterface::class);
        $products->expects($this->once())->method('load')->with([1000, 1001])->willReturn(
            $adapter->createFulfilled([['id' => 1000], ['id' => 1001]]),
        );
        $query = $this->createQuery($identities, $products);

        $result = $adapter->wait($query->productsConnectionByProductListQuery(
            $this->createStub(ProductList::class),
            $this->createArgument(['first' => 1, 'after' => (new ConnectionBuilder())->offsetToCursor(999)]),
        ));

        $this->assertSame([1001], array_map(static fn ($edge): int => $edge->getNode()['id'], $result->getEdges()));
        $this->assertSame(1001, $result->getTotalCount());
    }

    /**
     * @return iterable<string, array{array<string, int>}>
     */
    public static function oversizedPages(): iterable
    {
        yield 'forward' => [['first' => 101]];

        yield 'backward' => [['last' => 101]];
    }

    /**
     * @param array<string, int> $arguments
     */
    #[DataProvider('oversizedPages')]
    public function testConnectionRejectsOversizedPages(array $arguments): void
    {
        $query = $this->createQuery($this->createStub(ProductListApiFacade::class), $this->createStub(DataLoaderInterface::class));

        $this->expectException(MaxAllowedLimitUserError::class);
        $query->productsConnectionByProductListQuery($this->createStub(ProductList::class), $this->createArgument($arguments));
    }

    /**
     * @param array<string, int|string> $values
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
        ?ProductListFacade $lists = null,
    ): ProductsQuery {
        $query = new ProductsQuery(
            $this->createStub(ProductFacade::class),
            $this->createStub(ProductFilterFacade::class),
            $this->createStub(ProductConnectionFactory::class),
            $products,
            $lists ?? $this->createStub(ProductListFacade::class),
            $this->createStub(ProductRepository::class),
            $this->createStub(ProductOrderingModeProvider::class),
            $this->createStub(ProductFilterDataFactory::class),
            $this->createStub(DataLoaderInterface::class),
            $this->createStub(BrandQuery::class),
            $this->createStub(CategoryQuery::class),
            $this->createStub(FlagQuery::class),
            $identities,
        );
        $query->autowirePageSizeValidator(new PageSizeValidator());

        return $query;
    }
}
