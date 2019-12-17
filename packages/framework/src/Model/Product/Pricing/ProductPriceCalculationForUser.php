<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductPriceCalculationForUser
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation
     */
    protected $productPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    protected $pricingGroupSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ProductPriceCalculation $productPriceCalculation,
        CurrentCustomerUser $currentCustomerUser,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        Domain $domain
    ) {
        $this->productPriceCalculation = $productPriceCalculation;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->domain = $domain;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function calculatePriceForCurrentUser(Product $product)
    {
        return $this->productPriceCalculation->calculatePrice(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     *
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function calculatePriceForCustomerUserAndDomainId(Product $product, $domainId, ?CustomerUser $customerUser = null)
    {
        if ($customerUser === null) {
            $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        } else {
            $pricingGroup = $customerUser->getPricingGroup();
        }

        return $this->productPriceCalculation->calculatePrice($product, $domainId, $pricingGroup);
    }
}
