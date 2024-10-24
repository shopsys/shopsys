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
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory $productFilterOptionsFactory
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     */
    public function __construct(
        protected readonly ProductFilterOptionsFactory $productFilterOptionsFactory,
        protected readonly ProductFilterFacade $productFilterFacade,
    ) {
    }

    /**
     * @param callable $retrieveProductClosure
     * @param int $countOfProducts
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Closure $getProductFilterConfigClosure
     * @param string|null $orderingMode
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
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

    /**
     * @param array $edges
     * @param \Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface|null $pageInfo
     * @param \Closure $getProductFilterConfigClosure
     * @param string|null $orderingMode
     * @param int|\GraphQL\Executor\Promise\Promise|null $totalCount
     * @param string $defaultOrderingMode
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
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

    /**
     * @param callable $retrieveProductClosure
     * @param int $countOfProducts
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string|null $orderingMode
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param callable $retrieveProductClosure
     * @param int $countOfProducts
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param mixed $searchText
     * @return \Closure
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Closure $retrieveProductClosure
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingMode
     * @param string $defaultOrderingMode
     * @param string $batchLoadDataId
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
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

    /**
     * @param callable $retrieveProductClosure
     * @param \Closure $productFilterOptionsClosure
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param string $batchLoadDataId
     * @param string $orderingMode
     * @param string $defaultOrderingMode
     * @return \GraphQL\Executor\Promise\Promise
     */
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

        $promise->then(function (ProductConnection $productConnection) use ($batchLoadDataId) {
            $productConnection->setTotalCount(ProductsBatchLoader::getTotalByBatchLoadDataId($batchLoadDataId));
        });

        return $promise;
    }

    /**
     * @param callable $retrieveProductClosure
     * @param \Closure $productFilterOptionsClosure
     * @param string $orderingMode
     * @param string $defaultOrderingMode
     * @return \Overblog\GraphQLBundle\Relay\Connection\Paginator
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @param \Closure $retrieveProductClosure
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingMode
     * @param string $defaultOrderingMode
     * @param string $batchLoadDataId
     * @return \GraphQL\Executor\Promise\Promise
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param \Closure $retrieveProductClosure
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingMode
     * @param string $defaultOrderingMode
     * @param string $batchLoadDataId
     * @return \GraphQL\Executor\Promise\Promise
     */
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
