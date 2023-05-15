<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductsQuery extends AbstractQuery
{
    protected const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     */
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ProductFilterFacade $productFilterFacade,
        protected readonly ProductConnectionFactory $productConnectionFactory,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function productsQuery(Argument $argument)
    {
        $search = $argument['search'] ?? '';

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForAll(
            $argument,
        );

        return $this->productConnectionFactory->createConnectionForAll(
            function ($offset, $limit) use ($argument, $productFilterData, $search) {
                return $this->productFacade->getFilteredProductsOnCurrentDomain(
                    $limit,
                    $offset,
                    $this->getOrderingModeFromArgument($argument),
                    $productFilterData,
                    $search,
                );
            },
            $this->productFacade->getFilteredProductsCountOnCurrentDomain($productFilterData, $search),
            $argument,
            $productFilterData,
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function productsByCategoryQuery(Argument $argument, Category $category)
    {
        $search = $argument['search'] ?? '';

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForCategory(
            $argument,
            $category,
        );

        return $this->productConnectionFactory->createConnectionForCategory(
            $category,
            function ($offset, $limit) use ($argument, $category, $productFilterData, $search) {
                return $this->productFacade->getFilteredProductsByCategory(
                    $category,
                    $limit,
                    $offset,
                    $this->getOrderingModeFromArgument($argument),
                    $productFilterData,
                    $search,
                );
            },
            $this->productFacade->getFilteredProductsByCategoryCount($category, $productFilterData, $search),
            $argument,
            $productFilterData,
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function productsByBrandQuery(Argument $argument, Brand $brand)
    {
        $search = $argument['search'] ?? '';

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForBrand(
            $argument,
            $brand,
        );

        return $this->productConnectionFactory->createConnectionForBrand(
            $brand,
            function ($offset, $limit) use ($argument, $brand, $productFilterData, $search) {
                return $this->productFacade->getFilteredProductsByBrand(
                    $brand,
                    $limit,
                    $offset,
                    $this->getOrderingModeFromArgument($argument),
                    $productFilterData,
                    $search,
                );
            },
            $this->productFacade->getFilteredProductsByBrandCount($brand, $productFilterData, $search),
            $argument,
            $productFilterData,
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false && $argument->offsetExists('last') === false) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string
     */
    protected function getOrderingModeFromArgument(Argument $argument): string
    {
        $orderingMode = $this->getDefaultOrderingMode($argument);

        if ($argument->offsetExists('orderingMode')) {
            $orderingMode = $argument->offsetGet('orderingMode');
        }

        return $orderingMode;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string
     */
    protected function getDefaultOrderingMode(Argument $argument): string
    {
        if (isset($argument['search'])) {
            return ProductListOrderingConfig::ORDER_BY_RELEVANCE;
        }

        return ProductListOrderingConfig::ORDER_BY_PRIORITY;
    }
}
