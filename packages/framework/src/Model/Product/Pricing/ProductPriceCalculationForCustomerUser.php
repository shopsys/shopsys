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
    public function __construct(
        protected readonly ProductPriceCalculation $productPriceCalculation,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly Domain $domain,
        protected readonly SpecialPriceFacade $specialPriceFacade,
    ) {
    }

    public function calculatePricesForCustomerUserAndDomainId(
        Product $product,
        int $domainId,
        ?CustomerUser $customerUser = null,
    ): ProductPricesResult {
        $pricingGroup = $this->getPricingGroupForCustomerUser($customerUser, $domainId);

        $basicPrice = $this->productPriceCalculation->calculatePrice(
            $product,
            $domainId,
            $pricingGroup,
        );

        $sellingPrice = $this->calculateSellingPrice($product, $domainId, $basicPrice, $pricingGroup);

        return new ProductPricesResult($basicPrice, $sellingPrice);
    }

    public function calculatePricesForCurrentUser(Product $product): ProductPricesResult
    {
        return $this->calculatePricesForCustomerUserAndDomainId(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->findCurrentCustomerUser(),
        );
    }

    protected function getPricingGroupForCustomerUser(?CustomerUser $customerUser, int $domainId): PricingGroup
    {
        if ($customerUser === null) {
            return $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        }

        return $customerUser->getPricingGroup();
    }

    protected function calculateSellingPrice(
        Product $product,
        int $domainId,
        ProductPriceInterface $basicPrice,
        PricingGroup $pricingGroup,
    ): ProductPriceInterface {
        $specialPrice = $this->specialPriceFacade->findRelevantSpecialPrice($product, $domainId, $basicPrice->getPrice());

        if ($specialPrice === null || $specialPrice->isFuturePrice()) {
            return $basicPrice;
        }

        return new ProductPrice($specialPrice->price, $pricingGroup, $basicPrice->isPriceFrom());
    }
}
