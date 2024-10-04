<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

trait PromoCodeAssertionTrait
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param array $promoCodeData
     */
    public static function assertPromoCode(PromoCode $promoCode, array $promoCodeData): void
    {
        self::assertEquals($promoCode->getCode(), $promoCodeData['code']);
        self::assertEquals($promoCode->getDiscountType(), $promoCodeData['type']);
    }
}
