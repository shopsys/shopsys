<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

class PromoCodeLimitResolver
{
    /**
     * @param \App\Model\Order\PromoCode\PromoCodeLimitRepository $promoCodeLimitRepository
     * @param \App\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator $calculator
     */
    public function __construct(
        private PromoCodeLimitRepository $promoCodeLimitRepository,
        private PromoCodeApplicableProductsTotalPriceCalculator $calculator,
    ) {
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return \App\Model\Order\PromoCode\PromoCodeLimit|null
     */
    public function getLimitByPromoCode(PromoCode $promoCode, array $quantifiedProducts): ?PromoCodeLimit
    {
        $totalCartPrice = $this->calculator->calculateTotalPrice($quantifiedProducts)->getPriceWithVat();

        return $this->promoCodeLimitRepository->getHighestLimitByPromoCodeAndTotalPrice(
            $promoCode,
            $totalCartPrice->getAmount(),
        );
    }
}
