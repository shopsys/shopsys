<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\List\ProductList;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Flag\FlagQuery;

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
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery $brandQuery
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery $categoryQuery
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Flag\FlagQuery $flagQuery
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
        protected readonly BrandQuery $brandQuery,
        protected readonly CategoryQuery $categoryQuery,
        protected readonly FlagQuery $flagQuery,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function productsByBrandQuery(Argument $argument, Brand $brand): Promise
    {
        PageSizeValidator::checkMaxPageSize($argument);

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForBrand(
            $argument,
            $brand,
        );
        $batchLoadDataId = Uuid::uuid4()->toString();

        return $this->productConnectionFactory->createConnectionPromiseForBrand(
            $brand,
            function ($offset, $limit) use ($argument, $productFilterData, $brand, $batchLoadDataId) {
                return $this->productsByEntitiesBatchLoader->load(
                    new ProductBatchLoadByEntityData(
                        $batchLoadDataId,
                        $brand->getId(),
                        Brand::class,
                        $limit,
                        $offset,
                        $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
                        $productFilterData,
                    ),
                );
            },
            $argument,
            $productFilterData,
            $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
            $this->productOrderingModeProvider->getDefaultOrderingMode($argument),
            $batchLoadDataId,
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
        } else {
            $category = $categoryOrReadyCategorySeoMix->getCategory();
            $readyCategorySeoMix = $categoryOrReadyCategorySeoMix;

            $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForCategory(
                $argument,
                $category,
            );

            $this->productFilterDataFactory->updateProductFilterDataFromReadyCategorySeoMix($categoryOrReadyCategorySeoMix, $productFilterData);

            $orderingMode = $categoryOrReadyCategorySeoMix->getOrdering();
            $defaultOrderingMode = $orderingMode;
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function productsByFlagQuery(Argument $argument, Flag $flag): Promise
    {
        PageSizeValidator::checkMaxPageSize($argument);

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForFlag(
            $argument,
            $flag,
        );

        $productFilterData->flags[] = $flag;
        $batchLoadDataId = Uuid::uuid4()->toString();

        return $this->productConnectionFactory->createConnectionPromiseForFlag(
            $flag,
            function ($offset, $limit) use ($argument, $productFilterData, $flag, $batchLoadDataId) {
                return $this->productsByEntitiesBatchLoader->load(
                    new ProductBatchLoadByEntityData(
                        $batchLoadDataId,
                        $flag->getId(),
                        Flag::class,
                        $limit,
                        $offset,
                        $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
                        $productFilterData,
                    ),
                );
            },
            $argument,
            $productFilterData,
            $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
            $this->productOrderingModeProvider->getDefaultOrderingMode($argument),
            $batchLoadDataId,
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection|\GraphQL\Executor\Promise\Promise
     */
    public function productsWithOverlyingEntityQuery(Argument $argument, ResolveInfo $info): ProductConnection|Promise
    {
        PageSizeValidator::checkMaxPageSize($argument);

        $this->setDefaultFirstOffsetIfNecessary($argument);

        if ($argument['categorySlug'] !== null) {
            $category = $this->categoryQuery->categoryOrSeoMixByUuidOrUrlSlugQuery($info, null, $argument['categorySlug']);

            return $this->productsByCategoryOrReadyCategorySeoMixQuery(
                $argument,
                $category,
            );
        }

        if ($argument['brandSlug'] !== null) {
            $brand = $this->brandQuery->brandByUuidOrUrlSlugQuery(null, $argument['brandSlug']);

            return $this->productsByBrandQuery(
                $argument,
                $brand,
            );
        }

        if ($argument['flagSlug'] !== null) {
            $flag = $this->flagQuery->flagByUuidOrUrlSlugQuery(null, $argument['flagSlug']);

            return $this->productsByFlagQuery(
                $argument,
                $flag,
            );
        }

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForAll(
            $argument,
        );

        return $this->productConnectionFactory->createConnectionForAll(
            function ($offset, $limit) use ($argument, $productFilterData) {
                return $this->productFacade->getFilteredProductsOnCurrentDomain(
                    $limit,
                    $offset,
                    $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
                    $productFilterData,
                );
            },
            $this->productFacade->getFilteredProductsCountOnCurrentDomain($productFilterData),
            $argument,
            $productFilterData,
            $this->productOrderingModeProvider->getOrderingModeFromArgument($argument),
        );
    }
}
