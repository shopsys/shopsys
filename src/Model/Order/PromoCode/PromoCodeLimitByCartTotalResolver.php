<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\Domain\Domain;
use App\Model\Product\Pricing\QuantifiedProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class PromoCodeLimitByCartTotalResolver
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    private ?Price $totalPrice = null;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitRepository
     */
    private $promoCodeLimitRepository;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Product\Pricing\QuantifiedProductPriceCalculation
     */
    private $quantifiedProductPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private $currentCustomerUser;

    /**
     * @var \App\Model\Order\PromoCode\ProductPromoCodeFiller
     */
    private ProductPromoCodeFiller $productPromoCodeFiller;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeLimitRepository $promoCodeLimitRepository
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\PromoCode\ProductPromoCodeFiller $productPromoCodeFiller
     */
    public function __construct(
        PromoCodeLimitRepository $promoCodeLimitRepository,
        Domain $domain,
        QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        CurrentCustomerUser $currentCustomerUser,
        ProductPromoCodeFiller $productPromoCodeFiller
    ) {
        $this->promoCodeLimitRepository = $promoCodeLimitRepository;
        $this->domain = $domain;
        $this->quantifiedProductPriceCalculation = $quantifiedProductPriceCalculation;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->productPromoCodeFiller = $productPromoCodeFiller;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return \App\Model\Order\PromoCode\PromoCodeLimit|null
     */
    public function getLimitByPromoCode(PromoCode $promoCode, array $quantifiedProducts): ?PromoCodeLimit
    {
        $totalCartPrice = $this->getApplicableProductsCartTotalPrice($promoCode, $quantifiedProducts)->getPriceWithVat()->getAmount();

        return $this->promoCodeLimitRepository->getHighestLimitByPromoCodeAndTotalPrice($promoCode, $totalCartPrice);
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function getApplicableProductsCartTotalPrice(PromoCode $promoCode, array $quantifiedProducts): Price
    {
        if ($this->totalPrice === null) {
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

            $this->totalPrice = $this->countTotalPrice($quantifiedProductsPrices);
        }

        return $this->totalPrice;
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
