<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class PromoCodeWithDiscount
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $discount
     */
    public function __construct(
        public readonly PromoCode $promoCode,
        public readonly Price $discount,
    ) {
    }
}
