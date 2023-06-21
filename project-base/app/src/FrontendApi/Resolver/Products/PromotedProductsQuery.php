<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products;

use App\FrontendApi\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProduct;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\PromotedProductsQuery as BasePromotedProductsQuery;

/**
 * @property \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
 */
class PromotedProductsQuery extends BasePromotedProductsQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        TopProductFacade $topProductFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        private readonly ProductFacade $productFacade,
    ) {
        parent::__construct($topProductFacade, $domain, $currentCustomerUser);
    }

    /**
     * @return array
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
