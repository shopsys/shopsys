<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Connection;

use App\FrontendApi\Model\Product\ProductsBatchLoader;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Flag\Flag;
use Closure;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory as BaseProductConnectionFactory;

/**
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterOptionsFactory $productFilterOptionsFactory
 * @method __construct(\App\FrontendApi\Model\Product\Filter\ProductFilterOptionsFactory $productFilterOptionsFactory, \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection createConnectionForBrand(\App\Model\Product\Brand\Brand $brand, callable $retrieveProductClosure, int $countOfProducts, \Overblog\GraphQLBundle\Definition\Argument $argument, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection createConnectionForCategory(\App\Model\Category\Category $category, callable $retrieveProductClosure, int $countOfProducts, \Overblog\GraphQLBundle\Definition\Argument $argument, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 */
class ProductConnectionFactory extends BaseProductConnectionFactory
{
    /**
     * @param \App\Model\Category\Category $category
     * @param \Closure $retrieveProductClosure
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingMode
     * @param string $defaultOrderingMode
     * @param string $batchLoadDataId
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
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
        ?ReadyCategorySeoMix $readyCategorySeoMix,
    ): Promise {
        $searchText = $argument['search'] ?? '';
        $productFilterOptionsClosure = function () use ($category, $productFilterData, $searchText, $readyCategorySeoMix) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForCategory(
                $category,
                $this->productFilterFacade->getProductFilterConfigForCategory($category, $searchText),
                $productFilterData,
                $searchText,
                $readyCategorySeoMix,
            );
        };

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $argument, $batchLoadDataId, $orderingMode, $defaultOrderingMode);
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
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
        $searchText = $argument['search'] ?? '';
        $productFilterOptionsClosure = function () use ($flag, $productFilterData, $searchText) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForFlag(
                $flag,
                $this->productFilterFacade->getProductFilterConfigForFlag($flag, $searchText),
                $productFilterData,
                $searchText,
            );
        };

        return $this->getConnectionPromise($retrieveProductClosure, $productFilterOptionsClosure, $argument, $batchLoadDataId, $orderingMode, $defaultOrderingMode);
    }

    /**
     * @param \App\Model\Product\Brand\Brand $brand
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
        $searchText = $argument['search'] ?? '';
        $productFilterOptionsClosure = function () use ($brand, $productFilterData, $searchText) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForBrand(
                $brand,
                $this->productFilterFacade->getProductFilterConfigForBrand($brand, $searchText),
                $productFilterData,
                $searchText,
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
    private function getConnectionPromise(
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
    private function createPaginator(
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
}
