<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory as BasePromoCodeDataFactory;

/**
 * @method \App\Model\Order\PromoCode\PromoCodeData create()
 * @method \App\Model\Order\PromoCode\PromoCodeData createFromPromoCode(\App\Model\Order\PromoCode\PromoCode $promoCode)
 * @method fillFromPromoCode(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData, \App\Model\Order\PromoCode\PromoCode $promoCode)
 */
class PromoCodeDataFactory extends BasePromoCodeDataFactory
{
    /**
     * @return \App\Model\Order\PromoCode\PromoCodeData
     */
    public function createInstance(): BasePromoCodeData
    {
        return new PromoCodeData();
    }
}
