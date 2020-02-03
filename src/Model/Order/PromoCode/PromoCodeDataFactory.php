<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory as BasePromoCodeDataFactory;

class PromoCodeDataFactory extends BasePromoCodeDataFactory
{
    /**
     * @return \App\Model\Order\PromoCode\PromoCodeData
     */
    public function create(): BasePromoCodeData
    {
        return new PromoCodeData();
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
    protected function fillFromPromoCode(BasePromoCodeData $promoCodeData, BasePromoCode $promoCode)
    {
        parent::fillFromPromoCode($promoCodeData, $promoCode);
        $promoCodeData->domainId = $promoCode->getDomainId();
        $promoCodeData->time_valid_from = $promoCode->getTimeValidFrom();
        $promoCodeData->time_valid_to = $promoCode->getTimeValidTo();
    }
}
