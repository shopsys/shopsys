<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products;

use App\Component\Deprecation\DeprecatedMethodException;
use App\FrontendApi\Resolver\Products\Flag\FlagQuery;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Filter\ProductFilterDataFactory;
use App\Model\Product\Flag\Flag;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand as BaseBrand;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsQuery as BaseProductsQuery;

/**
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
 * @property \App\FrontendApi\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
 * @method setProductFilterFacade(\App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade)
 * @method setProductConnectionFactory(\App\FrontendApi\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory)
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
 * @method \GraphQL\Executor\Promise\Promise productsByCategoryOrReadyCategorySeoMixQuery(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Category\Category|\Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix)
 * @method \GraphQL\Executor\Promise\Promise getPromiseByCategory(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingMode, string $defaultOrderingMode, \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix)
 */
class ProductsQuery extends BaseProductsQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     * @param \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \App\FrontendApi\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $productsVisibleAndSortedByIdsBatchLoader
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductOrderingModeProvider $productOrderingModeProvider
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     * @param \Overblog\DataLoader\DataLoaderInterface $productsByEntitiesBatchLoader
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryQuery $categoryQuery
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery $brandQuery
     * @param \App\FrontendApi\Resolver\Products\Flag\FlagQuery $flagQuery
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductFilterFacade $productFilterFacade,
        ProductConnectionFactory $productConnectionFactory,
        ProductListFacade $productListFacade,
        DataLoaderInterface $productsVisibleAndSortedByIdsBatchLoader,
        ProductRepository $productRepository,
        ProductOrderingModeProvider $productOrderingModeProvider,
        ProductFilterDataFactory $productFilterDataFactory,
        DataLoaderInterface $productsByEntitiesBatchLoader,
        private readonly CategoryQuery $categoryQuery,
        private readonly BrandQuery $brandQuery,
        private readonly FlagQuery $flagQuery,
    ) {
        parent::__construct(
            $productFacade,
            $productFilterFacade,
            $productConnectionFactory,
            $productsVisibleAndSortedByIdsBatchLoader,
            $productListFacade,
            $productRepository,
            $productOrderingModeProvider,
            $productFilterDataFactory,
            $productsByEntitiesBatchLoader,
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Flag\Flag $flag
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
     * @return never
     */
    public function productsQuery(Argument $argument): never
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection|\GraphQL\Executor\Promise\Promise
     */
    public function productsWithOverlyingEntityQuery(Argument $argument, ResolveInfo $info)
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
            /** @var \App\Model\Product\Brand\Brand $brand */
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Brand\Brand $brand
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function productsByBrandQuery(Argument $argument, BaseBrand $brand): Promise
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
}
