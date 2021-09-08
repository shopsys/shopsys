<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Products;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Filter\ProductFilterDataFactory;
use InvalidArgumentException;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsResolver as BaseProductsResolver;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
 * @property \App\FrontendApi\Model\Product\ProductFacade|null $productFacade
 * @method setProductFacade(\App\FrontendApi\Model\Product\ProductFacade $productFacade)
 * @method \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object resolveByCategory(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Category\Category $category)
 * @method \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object resolveByBrand(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Product\Brand\Brand $brand)
 */
class ProductsResolver extends BaseProductsResolver
{
    /**
     * @var \App\Model\Product\Filter\ProductFilterDataFactory
     */
    private ProductFilterDataFactory $productFilterDataFactory;

    /**
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     * @param \App\FrontendApi\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     */
    public function __construct(
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ProductFacade $productFacade,
        ProductFilterFacade $productFilterFacade,
        ProductConnectionFactory $productConnectionFactory,
        ProductFilterDataFactory $productFilterDataFactory
    ) {
        parent::__construct($productOnCurrentDomainFacade, $productFacade, $productFilterFacade, $productConnectionFactory);

        $this->productFilterDataFactory = $productFilterDataFactory;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
    public function resolveByCategoryOrReadyCategorySeoMix(Argument $argument, $categoryOrReadyCategorySeoMix): ProductConnection
    {
        $seoMixOrderingMode = null;
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $category = $categoryOrReadyCategorySeoMix;
            /** @var \App\Model\Product\Filter\ProductFilterData $productFilterData */
            $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForCategory(
                $argument,
                $category
            );
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $category = $categoryOrReadyCategorySeoMix->getCategory();
            $seoMixOrderingMode = $categoryOrReadyCategorySeoMix->getOrdering();
            $productFilterData = $this->productFilterDataFactory->createProductFilterDataFromReadyCategorySeoMix($categoryOrReadyCategorySeoMix);
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class
                ),
            );
        }

        $search = $argument['search'] ?? '';

        $this->setDefaultFirstOffsetIfNecessary($argument);

        return $this->productConnectionFactory->createConnectionForCategory(
            $category,
            function ($offset, $limit) use ($argument, $category, $productFilterData, $search, $seoMixOrderingMode) {
                return $this->productFacade->getFilteredProductsByCategory(
                    $category,
                    $limit,
                    $offset,
                    $seoMixOrderingMode ?? $this->getOrderingModeFromArgument($argument),
                    $productFilterData,
                    $search
                );
            },
            $this->productFacade->getFilteredProductsByCategoryCount($category, $productFilterData, $search),
            $argument,
            $productFilterData
        );
    }
}
