<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BestsellingProductsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade $cachedBestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Overblog\DataLoader\DataLoaderInterface $productsSellableByIdsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider $productFrontendLimitProvider
     */
    public function __construct(
        protected readonly CachedBestsellingProductFacade $cachedBestsellingProductFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly DataLoaderInterface $productsSellableByIdsBatchLoader,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function bestSellingProductsByCategoryQuery(
        Category $category,
    ): Promise {
        $bestsellingProductsIds = $this->cachedBestsellingProductFacade->getOfferedBestsellingProductIds(
            $this->domain->getId(),
            $category,
            $this->currentCustomerUser->getPricingGroup(),
            $this->productFrontendLimitProvider->getProductsFrontendLimit(),
        );

        return $this->productsSellableByIdsBatchLoader->load($bestsellingProductsIds);
    }
}
