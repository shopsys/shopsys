<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Connection;

use App\Model\Product\Brand\Brand;
use App\Model\Product\Flag\Flag;
use Closure;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory as BaseProductConnectionFactory;

/**
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterOptionsFactory $productFilterOptionsFactory
 * @method __construct(\App\FrontendApi\Model\Product\Filter\ProductFilterOptionsFactory $productFilterOptionsFactory, \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade)
 * @method \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection createConnectionForBrand(\App\Model\Product\Brand\Brand $brand, callable $retrieveProductClosure, int $countOfProducts, \Overblog\GraphQLBundle\Definition\Argument $argument, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \GraphQL\Executor\Promise\Promise createConnectionPromiseForCategory(\App\Model\Category\Category $category, \Closure $retrieveProductClosure, \Overblog\GraphQLBundle\Definition\Argument $argument, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingMode, string $defaultOrderingMode, string $batchLoadDataId, \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix = null)
 */
class ProductConnectionFactory extends BaseProductConnectionFactory
{
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
