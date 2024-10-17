<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use InvalidArgumentException;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData;
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     * @param \Overblog\DataLoader\DataLoaderInterface $productsByEntitiesBatchLoader
     */
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ProductFilterFacade $productFilterFacade,
        protected readonly ProductConnectionFactory $productConnectionFactory,
        protected readonly DataLoaderInterface $productsVisibleAndSortedByIdsBatchLoader,
        protected readonly ProductListFacade $productListFacade,
        protected readonly ProductRepository $productRepository,
        protected readonly ProductOrderingModeProvider $productOrderingModeProvider,
        protected readonly ProductFilterDataFactory $productFilterDataFactory,
        protected readonly DataLoaderInterface $productsByEntitiesBatchLoader,
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|\Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function productsByCategoryOrReadyCategorySeoMixQuery(
        Argument $argument,
        Category|ReadyCategorySeoMix $categoryOrReadyCategorySeoMix,
    ): Promise {
        PageSizeValidator::checkMaxPageSize($argument);

        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $category = $categoryOrReadyCategorySeoMix;
            $readyCategorySeoMix = null;
            $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForCategory(
                $argument,
                $category,
            );
            $orderingMode = $this->productOrderingModeProvider->getOrderingModeFromArgument($argument);
            $defaultOrderingMode = $this->productOrderingModeProvider->getDefaultOrderingMode($argument);
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $category = $categoryOrReadyCategorySeoMix->getCategory();
            $readyCategorySeoMix = $categoryOrReadyCategorySeoMix;

            $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForCategory(
                $argument,
                $category,
            );

            $this->productFilterDataFactory->updateProductFilterDataFromReadyCategorySeoMix($categoryOrReadyCategorySeoMix, $productFilterData);

            $orderingMode = $categoryOrReadyCategorySeoMix->getOrdering();
            $defaultOrderingMode = $orderingMode;
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class,
                ),
            );
        }

        return $this->getPromiseByCategory($argument, $category, $productFilterData, $orderingMode, $defaultOrderingMode, $readyCategorySeoMix);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingMode
     * @param string $defaultOrderingMode
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    protected function getPromiseByCategory(
        Argument $argument,
        Category $category,
        ProductFilterData $productFilterData,
        string $orderingMode,
        string $defaultOrderingMode,
        ?ReadyCategorySeoMix $readyCategorySeoMix,
    ): Promise {
        $this->setDefaultFirstOffsetIfNecessary($argument);
        $batchLoadDataId = Uuid::uuid4()->toString();

        return $this->productConnectionFactory->createConnectionPromiseForCategory(
            $category,
            function ($offset, $limit) use ($argument, $category, $productFilterData, $orderingMode, $batchLoadDataId) {
                return $this->productsByEntitiesBatchLoader->load(
                    new ProductBatchLoadByEntityData(
                        $batchLoadDataId,
                        $category->getId(),
                        Category::class,
                        $limit,
                        $offset,
                        $orderingMode,
                        $productFilterData,
                        $argument['search'] ?? '',
                    ),
                );
            },
            $argument,
            $productFilterData,
            $orderingMode,
            $defaultOrderingMode,
            $batchLoadDataId,
            $readyCategorySeoMix,
        );
    }
}
