<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use GraphQL\Executor\Promise\Promise;
use InvalidArgumentException;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BestsellingProductsQuery extends AbstractQuery
{
    protected const BESTSELLING_PRODUCTS_FRONTEND_LIMIT = 30;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade $cachedBestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Overblog\DataLoader\DataLoaderInterface $productsSellableByIdsBatchLoader
     */
    public function __construct(
        protected readonly CachedBestsellingProductFacade $cachedBestsellingProductFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly DataLoaderInterface $productsSellableByIdsBatchLoader,
    ) {
    }

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function bestSellingProductsByCategoryOrReadyCategorySeoMixQuery(
        Category|ReadyCategorySeoMix $categoryOrReadyCategorySeoMix,
    ): Promise {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $category = $categoryOrReadyCategorySeoMix;
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $category = $categoryOrReadyCategorySeoMix->getCategory();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class,
                ),
            );
        }

        $bestsellingProductsIds = $this->cachedBestsellingProductFacade->getOfferedBestsellingProductIds(
            $this->domain->getId(),
            $category,
            $this->currentCustomerUser->getPricingGroup(),
            static::BESTSELLING_PRODUCTS_FRONTEND_LIMIT,
        );

        return $this->productsSellableByIdsBatchLoader->load($bestsellingProductsIds);
    }
}
