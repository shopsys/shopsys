<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductPriceCalculationForUser
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation
     */
    private $productPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private $pricingGroupSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        ProductPriceCalculation $productPriceCalculation,
        CurrentCustomer $currentCustomer,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        Domain $domain
    ) {
        $this->productPriceCalculation = $productPriceCalculation;
        $this->currentCustomer = $currentCustomer;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->domain = $domain;
    }

    public function calculatePriceForCurrentUser(Product $product): \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
    {
        return $this->productPriceCalculation->calculatePrice(
            $product,
            $this->domain->getId(),
            $this->currentCustomer->getPricingGroup()
        );
    }

    public function calculatePriceForUserAndDomainId(Product $product, int $domainId, User $user = null): \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
    {
        if ($user === null) {
            $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        } else {
            $pricingGroup = $user->getPricingGroup();
        }

        return $this->productPriceCalculation->calculatePrice($product, $domainId, $pricingGroup);
    }
}
