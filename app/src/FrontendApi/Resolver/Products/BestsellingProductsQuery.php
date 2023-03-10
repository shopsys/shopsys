<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products;

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
    /**
     * @param \App\Model\Product\BestsellingProduct\CachedBestsellingProductFacade $cachedBestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Overblog\DataLoader\DataLoaderInterface $productsSellableByIdsBatchLoader
     */
    public function __construct(
        private readonly CachedBestsellingProductFacade $cachedBestsellingProductFacade,
        private readonly Domain $domain,
        private readonly CurrentCustomerUser $currentCustomerUser,
        private readonly DataLoaderInterface $productsSellableByIdsBatchLoader
    ) {
    }

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function bestSellingProductsByCategoryOrReadyCategorySeoMixQuery($categoryOrReadyCategorySeoMix): Promise
    {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $category = $categoryOrReadyCategorySeoMix;
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $category = $categoryOrReadyCategorySeoMix->getCategory();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class
                ),
            );
        }

        $bestsellingProductsIds = $this->cachedBestsellingProductFacade->getAllOfferedBestsellingProductIds(
            $this->domain->getId(),
            $category,
            $this->currentCustomerUser->getPricingGroup()
        );

        return $this->productsSellableByIdsBatchLoader->load($bestsellingProductsIds);
    }
}
