<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\BestsellingProductsQuery as BaseBestsellingProductsQuery;

/**
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade $cachedBestsellingProductFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Overblog\DataLoader\DataLoaderInterface $productsSellableByIdsBatchLoader, \Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider $productFrontendLimitProvider)
 */
class BestsellingProductsQuery extends BaseBestsellingProductsQuery
{
    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function bestSellingProductsByCategoryQuery(
        Category|ReadyCategorySeoMix $categoryOrReadyCategorySeoMix,
    ): Promise {
        $category = $categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix
            ? $categoryOrReadyCategorySeoMix->getCategory()
            : $categoryOrReadyCategorySeoMix;

        return parent::bestSellingProductsByCategoryQuery($category);
    }
}
