<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Component\Domain\Domain;
use App\Model\Cart\CartFacade;
use App\Model\Product\Pricing\QuantifiedProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class PromoCodeLimitByCartTotalResolver
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitRepository
     */
    private $promoCodeLimitRepository;

    /**
     * @var \App\Model\Cart\CartFacade
     */
    private $cartFacade;

    /**
     * @var \App\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    private $currentPromoCodeFacade;

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
     * @param \App\Model\Order\PromoCode\PromoCodeLimitRepository $promoCodeLimitRepository
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        PromoCodeLimitRepository $promoCodeLimitRepository,
        CartFacade $cartFacade,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        Domain $domain,
        QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->promoCodeLimitRepository = $promoCodeLimitRepository;
        $this->cartFacade = $cartFacade;
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
        $this->domain = $domain;
        $this->quantifiedProductPriceCalculation = $quantifiedProductPriceCalculation;
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @return \App\Model\Order\PromoCode\PromoCodeLimit
     */
    public function getLimitByPromoCode(PromoCode $promoCode): PromoCodeLimit
    {
        $totalCartPrice = $this->getApplicableProductsCartTotalPrice()->getPriceWithVat()->getAmount();

        return $this->promoCodeLimitRepository->getHighestLimitByPromoCodeAndTotalPrice($promoCode, $totalCartPrice);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function getApplicableProductsCartTotalPrice(): Price
    {
        $domainId = $this->domain->getId();
        $currentCustomer = $this->currentCustomerUser->findCurrentCustomerUser();
        $quantifiedProducts = $this->cartFacade->getQuantifiedProductsOfCurrentCustomer();
        $promoCodePerProduct = $this->currentPromoCodeFacade->getPromoCodePerProductByDomainId(
            $quantifiedProducts,
            $domainId
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
