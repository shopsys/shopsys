<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Products;

use App\FrontendApi\Model\Product\ProductFacade;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\PromotedProductsResolver as BasePromotedProductsResolver;

class PromotedProductsResolver extends BasePromotedProductsResolver
{
    /**
     * @var \App\FrontendApi\Model\Product\ProductFacade
     */
    private ProductFacade $productFacade;

    /**
     * @param \App\Model\Product\TopProduct\TopProductFacade $topProductFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        TopProductFacade $topProductFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        ProductFacade $productFacade
    ) {
        parent::__construct($topProductFacade, $domain, $currentCustomerUser);

        $this->productFacade = $productFacade;
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        $allOfferedProducts = $this->topProductFacade->getAllOfferedProducts(
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );

        return $this->productFacade->getSellableProductsByIds(
            array_map(
                static function (Product $product) {
                    return $product->getId();
                },
                $allOfferedProducts
            )
        );
    }
}
