<?php

declare(strict_types=1);

namespace App\Model\Product\Pricing;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser as BaseProductPriceCalculationForCustomerUser;

/**
 * @property \App\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
 * @method __construct(\App\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation, \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade, \App\Component\Domain\Domain $domain)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice calculatePriceForCurrentUser(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice calculatePriceForCustomerUserAndDomainId(\App\Model\Product\Product $product, int $domainId, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @property \App\Component\Domain\Domain $domain
 */
class ProductPriceCalculationForCustomerUser extends BaseProductPriceCalculationForCustomerUser
{
    /**
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function calculateNonSellingPriceForCurrentUser(Product $product): ProductPrice
    {
        return $this->productPriceCalculation->calculateProductNonSellingPrice($product, $this->domain->getId());
    }
}
