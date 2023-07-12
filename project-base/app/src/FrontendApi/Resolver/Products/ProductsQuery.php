<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products;

use App\Component\Deprecation\DeprecatedMethodException;
use App\FrontendApi\Component\Validation\PageSizeValidator;
use App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData;
use App\FrontendApi\Resolver\Category\CategoryQuery;
use App\FrontendApi\Resolver\Products\Flag\FlagQuery;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Comparison\Comparison;
use App\Model\Product\Comparison\ComparisonRepository;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Filter\ProductFilterDataFactory;
use App\Model\Product\Flag\Flag;
use App\Model\Product\ProductRepository;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use InvalidArgumentException;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand as BaseBrand;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsQuery as BaseProductsQuery;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
 * @property \App\FrontendApi\Model\Product\ProductFacade|null $productFacade
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterFacade|null $productFilterFacade
 * @property \App\FrontendApi\Model\Product\Connection\ProductConnectionFactory|null $productConnectionFactory
 * @method setProductFacade(\App\FrontendApi\Model\Product\ProductFacade $productFacade)
 * @method setProductFilterFacade(\App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade)
 * @method setProductConnectionFactory(\App\FrontendApi\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory)
 */
class ProductsQuery extends BaseProductsQuery
{
    /**
     * @param \App\FrontendApi\Model\Product\ProductFacade $productFacade
     * @param \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \App\FrontendApi\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     * @param \Overblog\DataLoader\DataLoaderInterface $productsByEntitiesBatchLoader
     * @param \App\Model\Product\Comparison\ComparisonRepository $comparisonRepository
     * @param \Overblog\DataLoader\DataLoaderInterface $productsVisibleAndSortedByIdsBatchLoader
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\FrontendApi\Resolver\Category\CategoryQuery $categoryQuery
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandQuery $brandQuery
     * @param \App\FrontendApi\Resolver\Products\Flag\FlagQuery $flagQuery
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductFilterFacade $productFilterFacade,
        ProductConnectionFactory $productConnectionFactory,
        private readonly ProductFilterDataFactory $productFilterDataFactory,
        private readonly DataLoaderInterface $productsByEntitiesBatchLoader,
        private readonly ComparisonRepository $comparisonRepository,
        private readonly DataLoaderInterface $productsVisibleAndSortedByIdsBatchLoader,
        private readonly ProductRepository $productRepository,
        protected readonly CategoryQuery $categoryQuery,
        protected readonly BrandQuery $brandQuery,
        protected readonly FlagQuery $flagQuery,
    ) {
        parent::__construct($productFacade, $productFilterFacade, $productConnectionFactory);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function productsByCategoryOrReadyCategorySeoMixQuery(
        Argument $argument,
        $categoryOrReadyCategorySeoMix,
    ): Promise {
        PageSizeValidator::checkMaxPageSize($argument);

        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $category = $categoryOrReadyCategorySeoMix;
            $readyCategorySeoMix = null;
            $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForCategory(
                $argument,
                $category,
            );
            $orderingMode = $this->getOrderingModeFromArgument($argument);
            $defaultOrderingMode = $this->getDefaultOrderingMode($argument);
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $category = $categoryOrReadyCategorySeoMix->getCategory();
            $readyCategorySeoMix = $categoryOrReadyCategorySeoMix;
            $productFilterData = $this->productFilterDataFactory->createProductFilterDataFromReadyCategorySeoMix($categoryOrReadyCategorySeoMix);
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
                        $this->getOrderingModeFromArgument($argument),
                        $productFilterData,
                        $argument['search'] ?? '',
                    ),
                );
            },
            $argument,
            $productFilterData,
            $this->getOrderingModeFromArgument($argument),
            $this->getDefaultOrderingMode($argument),
            $batchLoadDataId,
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    public function productsQuery(Argument $argument)
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \GraphQL\Type\Definition\ResolveInfo $info
     * @return \App\FrontendApi\Model\Product\Connection\ProductExtendedConnection|\GraphQL\Executor\Promise\Promise
     */
    public function productsWithOverlyingEntityQuery(Argument $argument, ResolveInfo $info)
    {
        PageSizeValidator::checkMaxPageSize($argument);

        $search = $argument['search'] ?? '';

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForAll(
            $argument,
        );

        if ($argument['categorySlug']) {
            $category = $this->categoryQuery->categoryOrSeoMixByUuidOrUrlSlugQuery($info, urlSlug: $argument['categorySlug']);

            return $this->productsByCategoryOrReadyCategorySeoMixQuery(
                $argument,
                $category,
            );
        }

        if ($argument['brandSlug']) {
            /** @var \App\Model\Product\Brand\Brand $brand */
            $brand = $this->brandQuery->brandByUuidOrUrlSlugQuery(urlSlug: $argument['brandSlug']);

            return $this->productsByBrandQuery(
                $argument,
                $brand,
            );
        }

        if ($argument['flagSlug']) {
            $flag = $this->flagQuery->flagByUuidOrUrlSlugQuery(urlSlug: $argument['flagSlug']);

            return $this->productsByFlagQuery(
                $argument,
                $flag,
            );
        }

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
            $this->getOrderingModeFromArgument($argument),
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Category\Category $category
     * @return \GraphQL\Executor\Promise\Promise
     * @deprecated Method is deprecated. Use "productsByCategoryOrReadyCategorySeoMixQuery()" instead.
     */
    public function productsByCategoryQuery(Argument $argument, BaseCategory $category)
    {
        throw new DeprecatedMethodException();
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
                        $this->getOrderingModeFromArgument($argument),
                        $productFilterData,
                        $argument['search'] ?? '',
                    ),
                );
            },
            $argument,
            $productFilterData,
            $this->getOrderingModeFromArgument($argument),
            $this->getDefaultOrderingMode($argument),
            $batchLoadDataId,
        );
    }

    /**
     * @param string[] $catnums
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function productsByCatnumsQuery($catnums): Promise
    {
        $productIds = $this->productRepository->getProductIdsByCatnums($catnums);

        return $this->productsVisibleAndSortedByIdsBatchLoader->load($productIds);
    }

    /**
     * @param \App\Model\Product\Comparison\Comparison $comparison
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function productsByComparisonQuery(Comparison $comparison): Promise
    {
        $productIds = $this->comparisonRepository->getProductIdsByComparison($comparison);

        return $this->productsVisibleAndSortedByIdsBatchLoader->load($productIds);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingMode
     * @param string $defaultOrderingMode
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    private function getPromiseByCategory(
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
     * @return string
     */
    protected function getOrderingModeFromArgument(Argument $argument): string
    {
        $orderingMode = $this->getDefaultOrderingMode($argument);

        if ($argument->offsetExists('orderingMode') && $argument->offsetGet('orderingMode') !== null) {
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

        return self::getDefaultOrderingModeForListing();
    }

    /**
     * @return string
     */
    public static function getDefaultOrderingModeForListing(): string
    {
        return ProductListOrderingConfig::ORDER_BY_PRIORITY;
    }
}
