<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Connection;

use Closure;
use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductsBatchLoader;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsBatchLoadData;

class ProductConnectionFactory
{
    public function __construct(
        protected readonly DataLoaderInterface $productFilterOptionsBatchLoader,
    ) {
    }

    protected function createConnection(
        callable $retrieveProductClosure,
        int $countOfProducts,
        Argument $argument,
        Closure $getProductFilterConfigClosure,
        ?string $orderingMode = null,
    ): ProductConnection {
        $paginator = new Paginator($retrieveProductClosure);
        $connection = $paginator->auto($argument, $countOfProducts);

        return $this->createConnectionWithoutPaginator(
            $connection->getEdges(),
            $connection->getPageInfo(),
            $getProductFilterConfigClosure,
            $orderingMode,
            $connection->getTotalCount(),
        );
    }

    public function createConnectionWithoutPaginator(
        array $edges,
        ?PageInfoInterface $pageInfo,
        Closure $getProductFilterConfigClosure,
        ?string $orderingMode,
        int|Promise|null $totalCount,
        string $defaultOrderingMode = ProductListOrderingConfig::ORDER_BY_PRIORITY,
    ): ProductConnection {
        return new ProductConnection(
            $edges,
            $pageInfo,
            $getProductFilterConfigClosure,
            $orderingMode,
            $totalCount,
            $defaultOrderingMode,
        );
    }

    public function createConnectionForAll(
        callable $retrieveProductClosure,
        int $countOfProducts,
        Argument $argument,
        ProductFilterData $productFilterData,
        ?string $orderingMode = null,
    ): ProductConnection {
        $searchText = $argument['searchInput']['search'] ?? '';

        return $this->createConnection(
            $retrieveProductClosure,
            $countOfProducts,
            $argument,
            $this->createProductFilterOptionsClosure(new ProductFilterOptionsBatchLoadData(null, $searchText, $productFilterData)),
            $orderingMode,
        );
    }

    protected function createProductFilterOptionsClosure(ProductFilterOptionsBatchLoadData $batchLoadData): Closure
    {
        return fn (): Promise => $this->productFilterOptionsBatchLoader->load($batchLoadData);
    }

    public function createConnectionPromiseForCategory(
        Category $category,
        Closure $retrieveProductClosure,
        Argument $argument,
        ProductFilterData $productFilterData,
        string $orderingMode,
        string $defaultOrderingMode,
        string $batchLoadDataId,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ): Promise {
        return $this->getConnectionPromise(
            $retrieveProductClosure,
            $this->createProductFilterOptionsClosure(new ProductFilterOptionsBatchLoadData($category, '', $productFilterData, $readyCategorySeoMix)),
            $argument,
            $batchLoadDataId,
            $orderingMode,
            $defaultOrderingMode,
        );
    }

    protected function getConnectionPromise(
        callable $retrieveProductClosure,
        Closure $productFilterOptionsClosure,
        Argument $argument,
        string $batchLoadDataId,
        string $orderingMode,
        string $defaultOrderingMode,
    ): Promise {
        $paginator = $this->createPaginator($retrieveProductClosure, $productFilterOptionsClosure, $orderingMode, $defaultOrderingMode);

        /** @var \GraphQL\Executor\Promise\Promise $promise */
        $promise = $paginator->auto($argument, 0); // actual total count is set after the promise is fulfilled

        $promise->then(function (ProductConnection $productConnection) use ($batchLoadDataId): void {
            $productConnection->setTotalCount(ExtendedClassNameResolver::resolve(ProductsBatchLoader::class)::getTotalByBatchLoadDataId($batchLoadDataId));
        });

        return $promise;
    }

    protected function createPaginator(
        callable $retrieveProductClosure,
        Closure $productFilterOptionsClosure,
        string $orderingMode,
        string $defaultOrderingMode,
    ): Paginator {
        return new Paginator(
            $retrieveProductClosure,
            Paginator::MODE_PROMISE,
            new ConnectionBuilder(null, function ($edges, $pageInfo) use ($productFilterOptionsClosure, $orderingMode, $defaultOrderingMode) {
                return new ProductConnection(
                    $edges,
                    $pageInfo,
                    $productFilterOptionsClosure,
                    $orderingMode,
                    null,
                    $defaultOrderingMode,
                );
            }),
        );
    }

    public function createConnectionPromiseForFlag(
        Flag $flag,
        Closure $retrieveProductClosure,
        Argument $argument,
        ProductFilterData $productFilterData,
        string $orderingMode,
        string $defaultOrderingMode,
        string $batchLoadDataId,
    ): Promise {
        return $this->getConnectionPromise(
            $retrieveProductClosure,
            $this->createProductFilterOptionsClosure(new ProductFilterOptionsBatchLoadData($flag, '', $productFilterData)),
            $argument,
            $batchLoadDataId,
            $orderingMode,
            $defaultOrderingMode,
        );
    }

    public function createConnectionPromiseForBrand(
        Brand $brand,
        Closure $retrieveProductClosure,
        Argument $argument,
        ProductFilterData $productFilterData,
        string $orderingMode,
        string $defaultOrderingMode,
        string $batchLoadDataId,
    ): Promise {
        return $this->getConnectionPromise(
            $retrieveProductClosure,
            $this->createProductFilterOptionsClosure(new ProductFilterOptionsBatchLoadData($brand, '', $productFilterData)),
            $argument,
            $batchLoadDataId,
            $orderingMode,
            $defaultOrderingMode,
        );
    }
}
