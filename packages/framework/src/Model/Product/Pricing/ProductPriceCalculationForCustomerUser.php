<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductPriceCalculationForCustomerUser
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade $specialPriceFacade
     */
    public function __construct(
        protected readonly ProductPriceCalculation $productPriceCalculation,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly Domain $domain,
        protected readonly SpecialPriceFacade $specialPriceFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface
     */
    public function calculatePriceForCurrentUser(Product $product): ProductPriceInterface
    {
        return $this->calculatePriceForPricingGroup(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface
     */
    public function calculateBasicPriceForCurrentUser(Product $product): ProductPriceInterface
    {
        return $this->productPriceCalculation->calculatePrice(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface
     */
    public function calculateBasicPriceForCustomerUserAndDomainId(
        Product $product,
        int $domainId,
        ?CustomerUser $customerUser = null,
    ): ProductPriceInterface {
        if ($customerUser === null) {
            $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        } else {
            $pricingGroup = $customerUser->getPricingGroup();
        }

        return $this->productPriceCalculation->calculatePrice(
            $product,
            $domainId,
            $pricingGroup,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface
     */
    public function calculatePriceForCustomerUserAndDomainId(
        Product $product,
        int $domainId,
        ?CustomerUser $customerUser = null,
    ): ProductPriceInterface {
        if ($customerUser === null) {
            $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        } else {
            $pricingGroup = $customerUser->getPricingGroup();
        }

        return $this->calculatePriceForPricingGroup($product, $domainId, $pricingGroup);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface
     */
    protected function calculatePriceForPricingGroup(
        Product $product,
        int $domainId,
        PricingGroup $pricingGroup,
    ): ProductPriceInterface {
        $basicPrice = $this->productPriceCalculation->calculatePrice(
            $product,
            $domainId,
            $pricingGroup,
        );

        $specialPrice = $this->specialPriceFacade->findRelevantSpecialPrice($product, $domainId, $basicPrice->getPrice());

        if ($specialPrice === null || $specialPrice->isFuturePrice()) {
            return $basicPrice;
        }

        return new ProductPrice($specialPrice->price, $pricingGroup, $basicPrice->isPriceFrom());
    }
}
