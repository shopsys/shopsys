<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Connection;

use App\FrontendApi\Model\Product\ProductsBatchLoader;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Flag\Flag;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData as BaseProductFilterData;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection as BaseProductConnection;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory as BaseProductConnectionFactory;

/**
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterOptionsFactory $productFilterOptionsFactory
 * @method __construct(\App\FrontendApi\Model\Product\Filter\ProductFilterOptionsFactory $productFilterOptionsFactory, \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection createConnectionForBrand(\App\Model\Product\Brand\Brand $brand, callable $retrieveProductClosure, int $countOfProducts, \Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection createConnectionForCategory(\App\Model\Category\Category $category, callable $retrieveProductClosure, int $countOfProducts, \Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 */
class ProductConnectionFactory extends BaseProductConnectionFactory
{
    /**
     * @param \App\Model\Category\Category $category
     * @param callable $retrieveProductClosure
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function createConnectionPromiseForCategory(
        Category $category,
        callable $retrieveProductClosure,
        Argument $argument,
        ProductFilterData $productFilterData
    ): Promise {
        $productFilterOptionsClosure = function () use ($category, $productFilterData) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForCategory(
                $category,
                $this->productFilterFacade->getProductFilterConfigForCategory($category),
                $productFilterData
            );
        };

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $argument, $category->getId());
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param callable $retrieveProductClosure
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function createConnectionPromiseForFlag(
        Flag $flag,
        callable $retrieveProductClosure,
        Argument $argument,
        ProductFilterData $productFilterData
    ): Promise {
        $productFilterOptionsClosure = function () use ($flag, $productFilterData) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForFlag(
                $flag,
                $this->productFilterFacade->getProductFilterConfigForFlag($flag),
                $productFilterData
            );
        };

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $argument, $flag->getId());
    }

    /**
     * @param \App\Model\Product\Brand\Brand $brand
     * @param callable $retrieveProductClosure
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function createConnectionPromiseForBrand(
        Brand $brand,
        callable $retrieveProductClosure,
        Argument $argument,
        ProductFilterData $productFilterData
    ): Promise {
        $productFilterOptionsClosure = function () use ($brand, $productFilterData) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForBrand(
                $brand,
                $this->productFilterFacade->getProductFilterConfigForBrand($brand),
                $productFilterData
            );
        };

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $argument, $brand->getId());
    }

    /**
     * @param callable $retrieveProductClosure
     * @param int $countOfProducts
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string|null $searchText
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
    public function createConnectionForAll(
        callable $retrieveProductClosure,
        int $countOfProducts,
        Argument $argument,
        BaseProductFilterData $productFilterData,
        ?string $searchText = null
    ): BaseProductConnection {
        $productFilterOptionsClosure = function () use ($productFilterData, $searchText) {
            if ($searchText === null) {
                $productFilterConfig = $this->productFilterFacade->getProductFilterConfigForAll();
            } else {
                $productFilterConfig = $this->productFilterFacade->getProductFilterConfigForSearch($searchText);
            }
            return $this->productFilterOptionsFactory->createProductFilterOptionsForAll(
                $productFilterConfig,
                $productFilterData,
                $searchText
            );
        };

        return $this->createConnection(
            $retrieveProductClosure,
            $countOfProducts,
            $argument,
            $productFilterOptionsClosure
        );
    }

    /**
     * @param callable $retrieveProductClosure
     * @param callable $productFilterOptionsClosure
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param int $entityId
     * @return \GraphQL\Executor\Promise\Promise
     */
    private function getConnectionPromise(
        callable $retrieveProductClosure,
        callable $productFilterOptionsClosure,
        Argument $argument,
        int $entityId
    ): Promise {
        $paginator = $this->createPaginator($retrieveProductClosure, $productFilterOptionsClosure);

        /** @var \GraphQL\Executor\Promise\Promise $promise */
        $promise = $paginator->auto($argument, 0); // actual total count is set after the promise is fulfilled

        $promise->then(function (ProductConnection $productConnection) use ($entityId) {
            $productConnection->setTotalCount(ProductsBatchLoader::getTotalByEntityId($entityId));
        });

        return $promise;
    }

    /**
     * @param callable $retrieveProductClosure
     * @param callable $productFilterOptionsClosure
     * @return \Overblog\GraphQLBundle\Relay\Connection\Paginator
     */
    private function createPaginator(
        callable $retrieveProductClosure,
        callable $productFilterOptionsClosure
    ): Paginator {
        return new Paginator(
            $retrieveProductClosure,
            Paginator::MODE_PROMISE,
            new ConnectionBuilder(function ($edges, $pageInfo) use ($productFilterOptionsClosure) {
                return new ProductConnection(
                    $edges,
                    $pageInfo,
                    $productFilterOptionsClosure
                );
            })
        );
    }
}
