<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Product;
use GraphQL\Executor\Promise\Promise;
use InvalidArgumentException;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade;

class BestsellingProductsResolver implements ResolverInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade
     */
    private CachedBestsellingProductFacade $cachedBestsellingProductFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \Overblog\DataLoader\DataLoaderInterface
     */
    private DataLoaderInterface $productsSellableByIdsBatchLoader;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade $cachedBestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Overblog\DataLoader\DataLoaderInterface $productsSellableByIdsBatchLoader
     */
    public function __construct(
        CachedBestsellingProductFacade $cachedBestsellingProductFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        DataLoaderInterface $productsSellableByIdsBatchLoader
    ) {
        $this->cachedBestsellingProductFacade = $cachedBestsellingProductFacade;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->productsSellableByIdsBatchLoader = $productsSellableByIdsBatchLoader;
    }

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveByCategoryOrReadyCategorySeoMix($categoryOrReadyCategorySeoMix): Promise
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

        /** @var \App\Model\Product\Product[] $bestsellingProducts */
        $bestsellingProducts = $this->cachedBestsellingProductFacade->getAllOfferedBestsellingProducts(
            $this->domain->getId(),
            $category,
            $this->currentCustomerUser->getPricingGroup()
        );

        $bestsellingProductsIds = array_map(
            static function (Product $product) {
                return $product->getId();
            },
            $bestsellingProducts
        );

        return $this->productsSellableByIdsBatchLoader->load($bestsellingProductsIds);
    }
}
