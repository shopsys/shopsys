<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory as BasePromoCodeDataFactory;

/**
 * @method \App\Model\Order\PromoCode\PromoCodeData createInstance()
 */
class PromoCodeDataFactory extends BasePromoCodeDataFactory
{
    /**
     * @return \App\Model\Order\PromoCode\PromoCodeData
     */
    public function create(): BasePromoCodeData
    {
        $promoCodeData = new PromoCodeData();
        $promoCodeData->massGenerate = false;

        return $promoCodeData;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @return \App\Model\Order\PromoCode\PromoCodeData
     */
    public function createFromPromoCode(BasePromoCode $promoCode): BasePromoCodeData
    {
        $promoCodeData = new PromoCodeData();
        $this->fillFromPromoCode($promoCodeData, $promoCode);

        return $promoCodeData;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    protected function fillFromPromoCode(BasePromoCodeData $promoCodeData, BasePromoCode $promoCode): void
    {
        parent::fillFromPromoCode($promoCodeData, $promoCode);

        $promoCodeData->massGenerate = $promoCode->isMassGenerate();
        $promoCodeData->prefix = $promoCode->getPrefix();
    }
}
