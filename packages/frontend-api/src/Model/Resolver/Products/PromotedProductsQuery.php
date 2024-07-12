<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProduct;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PromotedProductsQuery extends AbstractQuery
{
    protected const PROMOTED_PRODUCTS_FRONTEND_LIMIT = 30;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        protected readonly TopProductFacade $topProductFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function promotedProductsQuery(): array
    {
        $allSortedPromotedProductsOnDomain = $this->topProductFacade->getAll($this->domain->getId());

        return $this->productFacade->getSellableProductsByIds(
            array_map(
                static function (TopProduct $product) {
                    return $product->getProduct()->getId();
                },
                $allSortedPromotedProductsOnDomain,
            ),
        );
    }
}
