<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator;

class PromoCodeLimitResolver
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository $promoCodeLimitRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeApplicableProductsTotalPriceCalculator $promoCodeApplicableProductsTotalPriceCalculator
     */
    public function __construct(
        protected readonly PromoCodeLimitRepository $promoCodeLimitRepository,
        protected readonly PromoCodeApplicableProductsTotalPriceCalculator $promoCodeApplicableProductsTotalPriceCalculator,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit|null
     */
    public function getLimitByPromoCode(PromoCode $promoCode, array $quantifiedProducts): ?PromoCodeLimit
    {
        $totalCartPrice = $this->promoCodeApplicableProductsTotalPriceCalculator->calculateTotalPrice($quantifiedProducts)->getPriceWithVat();

        return $this->promoCodeLimitRepository->getHighestLimitByPromoCodeAndTotalPrice(
            $promoCode,
            $totalCartPrice->getAmount(),
        );
    }
}
