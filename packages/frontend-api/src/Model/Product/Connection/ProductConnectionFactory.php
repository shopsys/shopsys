<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Connection;

use Closure;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
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

        return new ProductConnection(
            $connection->getEdges(),
            $connection->getPageInfo(),
            $getProductFilterConfigClosure,
            $orderingMode,
            $connection->getTotalCount(),
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
        $searchText = $argument['search'] ?? '';
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
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param callable $retrieveProductClosure
     * @param int $countOfProducts
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
    public function createConnectionForCategory(
        Category $category,
        callable $retrieveProductClosure,
        int $countOfProducts,
        Argument $argument,
        ProductFilterData $productFilterData,
    ): ProductConnection {
        $productFilterOptionsClosure = function () use ($category, $productFilterData) {
            return $this->productFilterOptionsFactory->createProductFilterOptionsForCategory(
                $category,
                $this->productFilterFacade->getProductFilterConfigForCategory($category),
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
     * @param array $products
     * @param string $search
     * @param int $offset
     * @param int $limit
     * @param int $countOfProducts
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string|null $orderingMode
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
    public function createConnectionForSearchFromArray(
        array $products,
        string $search,
        int $offset,
        int $limit,
        int $countOfProducts,
        ProductFilterData $productFilterData,
        ?string $orderingMode = null,
    ): ProductConnection {
        $connectionBuilder = new ConnectionBuilder();
        $connection = $connectionBuilder->connectionFromArray($products);

        $pageInfo = $connection->getPageInfo();
        $pageInfo->setHasPreviousPage($offset > 0);
        $pageInfo->setHasNextPage($offset + $limit < $countOfProducts);

        return new ProductConnection(
            $connection->getEdges(),
            $pageInfo,
            $this->getProductFilterOptionsClosure($productFilterData, $search),
            $orderingMode,
            $countOfProducts,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param mixed $searchText
     * @return \Closure
     */
    protected function getProductFilterOptionsClosure(ProductFilterData $productFilterData, mixed $searchText): Closure
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
}
