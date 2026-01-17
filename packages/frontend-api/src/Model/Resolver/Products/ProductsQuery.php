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

    public function productsByBrandQuery(Argument $argument, Brand $brand): Promise
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);

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
                        $brand,
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

    public function productsByProductListQuery(ProductList $productList): Promise
    {
        $productIds = $this->productListFacade->getProductIdsByProductList($productList);

        return $this->productsVisibleAndSortedByIdsBatchLoader->load($productIds);
    }

    /**
     * @param string[] $catnums
     */
    public function productsByCatnumsQuery(array $catnums): Promise
    {
        $productIds = $this->productRepository->getProductIdsByCatnums($catnums);

        return $this->productsVisibleAndSortedByIdsBatchLoader->load($productIds);
    }

    public function productsByCategoryOrReadyCategorySeoMixQuery(
        Argument $argument,
        Category|ReadyCategorySeoMix $categoryOrReadyCategorySeoMix,
    ): Promise {
        $this->pageSizeValidator->checkMaxPageSize($argument);

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
                        $category,
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

    public function productsByFlagQuery(Argument $argument, Flag $flag): Promise
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);

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
                        $flag,
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

    public function productsWithOverlyingEntityQuery(Argument $argument, ResolveInfo $info): ProductConnection|Promise
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);

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
