<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\Domain\Domain;
use App\Model\Product\Pricing\QuantifiedProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class PromoCodeApplicableProductsTotalPriceCalculator
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\Model\Order\PromoCode\ProductPromoCodeFiller
     */
    private ProductPromoCodeFiller $productPromoCodeFiller;

    /**
     * @var \App\Model\Product\Pricing\QuantifiedProductPriceCalculation
     */
    private QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation;

    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\ProductPromoCodeFiller $productPromoCodeFiller
     * @param \App\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        CurrentCustomerUser $currentCustomerUser,
        ProductPromoCodeFiller $productPromoCodeFiller,
        QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        Domain $domain
    ) {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->productPromoCodeFiller = $productPromoCodeFiller;
        $this->quantifiedProductPriceCalculation = $quantifiedProductPriceCalculation;
        $this->domain = $domain;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param array $quantifiedProducts
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculateTotalPrice(PromoCode $promoCode, array $quantifiedProducts): Price
    {
        $domainId = $this->domain->getId();
        $currentCustomer = $this->currentCustomerUser->findCurrentCustomerUser();
        $promoCodePerProduct = $this->productPromoCodeFiller->getPromoCodePerProductByDomainId(
            $quantifiedProducts,
            $domainId,
            $promoCode
        );
        $quantifiedProductsPrices = $this->quantifiedProductPriceCalculation->calculatePromoCodeApplicablePrices(
            $quantifiedProducts,
            $domainId,
            $currentCustomer,
            $promoCodePerProduct
        );

        return $this->countTotalPrice($quantifiedProductsPrices);
    }

    /**
     * @param \App\Model\Order\Item\QuantifiedItemPrice[] $quantifiedProductsPrices
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function countTotalPrice(array $quantifiedProductsPrices): Price
    {
        $finalPrice = Price::zero();

        foreach ($quantifiedProductsPrices as $quantifiedProductPrice) {
            $finalPrice = $finalPrice->add($quantifiedProductPrice->getTotalPrice());
        }

        return $finalPrice;
    }
}
