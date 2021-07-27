<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Products;

use App\FrontendApi\Model\Product\ProductFacade;
use App\Model\Product\Product;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade;

class BestsellingProductsResolver implements ResolverInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade
     */
    private CachedBestsellingProductFacade $cachedBestsellingProductFacade;

    /**
     * @var \App\FrontendApi\Model\Product\ProductFacade
     */
    private ProductFacade $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade $cachedBestsellingProductFacade
     * @param \App\FrontendApi\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        CachedBestsellingProductFacade $cachedBestsellingProductFacade,
        ProductFacade $productFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->cachedBestsellingProductFacade = $cachedBestsellingProductFacade;
        $this->productFacade = $productFacade;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return array
     */
    public function resolveByCategory(Category $category): array
    {
        $bestsellingProducts = $this->cachedBestsellingProductFacade->getAllOfferedBestsellingProducts(
            $this->domain->getId(),
            $category,
            $this->currentCustomerUser->getPricingGroup()
        );

        return $this->productFacade->getSellableProductsByIds(array_map(
            static function (Product $product) {
                return $product->getId();
            },
            $bestsellingProducts
        ));
    }
}
