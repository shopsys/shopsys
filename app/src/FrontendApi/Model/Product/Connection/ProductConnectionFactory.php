<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Connection;

use App\Model\Category\Category;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Flag\Flag;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData as BaseProductFilterData;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
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
     * @param int $countOfProducts
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function createConnectionPromiseForCategory(
        Category $category,
        callable $retrieveProductClosure,
        int $countOfProducts,
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

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $countOfProducts, $argument);
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param callable $retrieveProductClosure
     * @param int $countOfProducts
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function createConnectionPromiseForFlag(
        Flag $flag,
        callable $retrieveProductClosure,
        int $countOfProducts,
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

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $countOfProducts, $argument);
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
    ): ProductConnection {
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
     * @param int $countOfProducts
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise
     */
    private function getConnectionPromise(
        callable $retrieveProductClosure,
        callable $productFilterOptionsClosure,
        int $countOfProducts,
        Argument $argument
    ): Promise {
        $paginator = $this->createPaginator($retrieveProductClosure, $productFilterOptionsClosure, $countOfProducts);

        /** @var \GraphQL\Executor\Promise\Promise $promise */
        $promise = $paginator->auto($argument, $countOfProducts);

        return $promise;
    }

    /**
     * @param callable $retrieveProductClosure
     * @param callable $productFilterOptionsClosure
     * @param int $countOfProducts
     * @return \Overblog\GraphQLBundle\Relay\Connection\Paginator
     */
    private function createPaginator(
        callable $retrieveProductClosure,
        callable $productFilterOptionsClosure,
        int $countOfProducts
    ): Paginator {
        return new Paginator(
            $retrieveProductClosure,
            Paginator::MODE_PROMISE,
            new ConnectionBuilder(function ($edges, $pageInfo) use ($productFilterOptionsClosure, $countOfProducts) {
                return new ProductConnection(
                    $edges,
                    $pageInfo,
                    $countOfProducts,
                    $productFilterOptionsClosure
                );
            })
        );
    }
}
