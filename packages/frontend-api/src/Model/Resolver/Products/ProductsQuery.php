<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \Overblog\DataLoader\DataLoaderInterface $productsVisibleAndSortedByIdsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider $productOrderingModeProvider
     */
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ProductFilterFacade $productFilterFacade,
        protected readonly ProductConnectionFactory $productConnectionFactory,
        protected readonly DataLoaderInterface $productsVisibleAndSortedByIdsBatchLoader,
        protected readonly ProductListFacade $productListFacade,
        protected readonly ProductRepository $productRepository,
        protected readonly ProductOrderingModeProvider $productOrderingModeProvider,
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
                    $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
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
                    $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
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
                    $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
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
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductList $productList
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function productsByProductListQuery(ProductList $productList): Promise
    {
        $productIds = $this->productListFacade->getProductIdsByProductList($productList);

        return $this->productsVisibleAndSortedByIdsBatchLoader->load($productIds);
    }

    /**
     * @param string[] $catnums
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function productsByCatnumsQuery(array $catnums): Promise
    {
        $productIds = $this->productRepository->getProductIdsByCatnums($catnums);

        return $this->productsVisibleAndSortedByIdsBatchLoader->load($productIds);
    }
}
