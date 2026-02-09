<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Connection;

use Closure;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductsBatchLoader;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory;

class ProductConnectionFactory
{
    public function __construct(
        protected readonly ProductFilterOptionsFactory $productFilterOptionsFactory,
        protected readonly ProductFilterFacade $productFilterFacade,
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
        $productFilterOptionsClosure = $this->getProductFilterOptionsClosure($productFilterData, $searchText);

        return $this->createConnection(
            $retrieveProductClosure,
            $countOfProducts,
            $argument,
            $productFilterOptionsClosure,
            $orderingMode,
        );
    }

    public function createConnectionForBrand(
        Brand $brand,
        callable $retrieveProductClosure,
        int $countOfProducts,
        Argument $argument,
        ProductFilterData $productFilterData,
    ): ProductConnection {
        $productFilterOptionsClosure = function () use ($brand, $productFilterData) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForBrand(
                $brand,
                $this->productFilterFacade->getProductFilterConfigForBrand($brand),
                $productFilterData,
            );
        };

        return $this->createConnection(
            $retrieveProductClosure,
            $countOfProducts,
            $argument,
            $productFilterOptionsClosure,
        );
    }

    public function getProductFilterOptionsClosure(ProductFilterData $productFilterData, mixed $searchText): Closure
    {
        return function () use ($productFilterData, $searchText) {
            if ($searchText === '') {
                $productFilterConfig = $this->productFilterFacade->getProductFilterConfigForAll();
            } else {
                $productFilterConfig = $this->productFilterFacade->getProductFilterConfigForSearch($searchText);
            }

            return $this->productFilterOptionsFactory->createProductFilterOptionsForAll(
                $productFilterConfig,
                $productFilterData,
                $searchText,
            );
        };
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
        $productFilterOptionsClosure = function () use ($category, $productFilterData, $readyCategorySeoMix) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForCategory(
                $category,
                $this->productFilterFacade->getProductFilterConfigForCategory($category),
                $productFilterData,
                $readyCategorySeoMix,
            );
        };

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $argument, $batchLoadDataId, $orderingMode, $defaultOrderingMode);
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
            $productConnection->setTotalCount(ProductsBatchLoader::getTotalByBatchLoadDataId($batchLoadDataId));
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
        $productFilterOptionsClosure = function () use ($flag, $productFilterData) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForFlag(
                $flag,
                $this->productFilterFacade->getProductFilterConfigForFlag($flag),
                $productFilterData,
            );
        };

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $argument, $batchLoadDataId, $orderingMode, $defaultOrderingMode);
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
        $productFilterOptionsClosure = function () use ($brand, $productFilterData) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForBrand(
                $brand,
                $this->productFilterFacade->getProductFilterConfigForBrand($brand),
                $productFilterData,
            );
        };

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $argument, $batchLoadDataId, $orderingMode, $defaultOrderingMode);
    }
}
