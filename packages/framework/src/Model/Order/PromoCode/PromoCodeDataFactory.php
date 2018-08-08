<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

class PromoCodeDataFactory implements PromoCodeDataFactoryInterface
{
    public function create(): PromoCodeData
    {
        return new PromoCodeData();
    }

    public function createFromPromoCode(PromoCode $promoCode): PromoCodeData
    {
        $promoCodeData = new PromoCodeData();
        $this->fillFromPromoCode($promoCodeData, $promoCode);

        return $promoCodeData;
    }

    protected function fillFromPromoCode(PromoCodeData $promoCodeData, PromoCode $promoCode)
    {
        $promoCodeData->code = $promoCode->getCode();
        $promoCodeData->percent = $promoCode->getPercent();
    }
}
