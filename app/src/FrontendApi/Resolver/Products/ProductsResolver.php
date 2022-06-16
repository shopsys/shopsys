<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use App\FrontendApi\Exception\DeprecatedMethodException;
use App\FrontendApi\Model\Product\BatchLoad\ProductBatchLoadByEntityData;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\Filter\ProductFilterDataFactory;
use App\Model\Product\Flag\Flag;
use GraphQL\Executor\Promise\Promise;
use InvalidArgumentException;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand as BaseBrand;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsResolver as BaseProductsResolver;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
 * @property \App\FrontendApi\Model\Product\ProductFacade|null $productFacade
 * @property \App\FrontendApi\Model\Product\Filter\ProductFilterFacade|null $productFilterFacade
 * @property \App\FrontendApi\Model\Product\Connection\ProductConnectionFactory|null $productConnectionFactory
 * @method setProductFacade(\App\FrontendApi\Model\Product\ProductFacade $productFacade)
 * @method setProductFilterFacade(\App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade)
 * @method setProductConnectionFactory(\App\FrontendApi\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory)
 */
class ProductsResolver extends BaseProductsResolver
{
    /**
     * @var \App\Model\Product\Filter\ProductFilterDataFactory
     */
    private ProductFilterDataFactory $productFilterDataFactory;

    /**
     * @var \Overblog\DataLoader\DataLoaderInterface
     */
    private DataLoaderInterface $productsByEntitiesBatchLoader;

    /**
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     * @param \App\FrontendApi\Model\Product\ProductFacade $productFacade
     * @param \App\FrontendApi\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \App\FrontendApi\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     * @param \Overblog\DataLoader\DataLoaderInterface $productsByEntitiesBatchLoader
     */
    public function __construct(
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ProductFacade $productFacade,
        ProductFilterFacade $productFilterFacade,
        ProductConnectionFactory $productConnectionFactory,
        ProductFilterDataFactory $productFilterDataFactory,
        DataLoaderInterface $productsByEntitiesBatchLoader
    ) {
        parent::__construct($productOnCurrentDomainFacade, $productFacade, $productFilterFacade, $productConnectionFactory);

        $this->productFilterDataFactory = $productFilterDataFactory;
        $this->productsByEntitiesBatchLoader = $productsByEntitiesBatchLoader;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveByCategoryOrReadyCategorySeoMix(Argument $argument, $categoryOrReadyCategorySeoMix): Promise
    {
        PageSizeValidator::checkMaxPageSize($argument);

        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $category = $categoryOrReadyCategorySeoMix;
            $readyCategorySeoMix = null;
            $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForCategory(
                $argument,
                $category
            );
            $orderingMode = $this->getOrderingModeFromArgument($argument);
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $category = $categoryOrReadyCategorySeoMix->getCategory();
            $readyCategorySeoMix = $categoryOrReadyCategorySeoMix;
            $productFilterData = $this->productFilterDataFactory->createProductFilterDataFromReadyCategorySeoMix($categoryOrReadyCategorySeoMix);
            $orderingMode = $categoryOrReadyCategorySeoMix->getOrdering();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class
                ),
            );
        }

        return $this->getPromiseByCategory($argument, $category, $productFilterData, $orderingMode, $readyCategorySeoMix);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Flag\Flag $flag
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveByFlag(Argument $argument, Flag $flag): Promise
    {
        PageSizeValidator::checkMaxPageSize($argument);

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForFlag(
            $argument,
            $flag
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
                        $argument['search'] ?? ''
                    )
                );
            },
            $argument,
            $productFilterData,
            $this->getOrderingModeFromArgument($argument),
            $batchLoadDataId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Argument $argument)
    {
        PageSizeValidator::checkMaxPageSize($argument);

        $search = $argument['search'] ?? '';

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForAll(
            $argument
        );

        return $this->productConnectionFactory->createConnectionForAll(
            function ($offset, $limit) use ($argument, $productFilterData, $search) {
                return $this->productFacade->getFilteredProductsOnCurrentDomain(
                    $limit,
                    $offset,
                    $this->getOrderingModeFromArgument($argument),
                    $productFilterData,
                    $search
                );
            },
            $this->productFacade->getFilteredProductsCountOnCurrentDomain($productFilterData, $search),
            $argument,
            $productFilterData,
            $this->getOrderingModeFromArgument($argument)
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Category\Category $category
     * @return \GraphQL\Executor\Promise\Promise
     * @deprecated Method is deprecated. Use "resolveByCategoryOrReadyCategorySeoMix()" instead.
     */
    public function resolveByCategory(Argument $argument, BaseCategory $category)
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Product\Brand\Brand $brand
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveByBrand(Argument $argument, BaseBrand $brand)
    {
        PageSizeValidator::checkMaxPageSize($argument);

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForBrand(
            $argument,
            $brand
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
                        $argument['search'] ?? ''
                    )
                );
            },
            $argument,
            $productFilterData,
            $this->getOrderingModeFromArgument($argument),
            $batchLoadDataId
        );
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        $aliases = parent::getAliases();

        $aliases['resolveByFlag'] = 'productsByFlag';

        return $aliases;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string|null $orderingMode
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    private function getPromiseByCategory(
        Argument $argument,
        Category $category,
        ProductFilterData $productFilterData,
        ?string $orderingMode,
        ?ReadyCategorySeoMix $readyCategorySeoMix
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
                        $orderingMode ?? $this->getOrderingModeFromArgument($argument),
                        $productFilterData,
                        $argument['search'] ?? ''
                    )
                );
            },
            $argument,
            $productFilterData,
            $orderingMode,
            $batchLoadDataId,
            $readyCategorySeoMix
        );
    }
}
