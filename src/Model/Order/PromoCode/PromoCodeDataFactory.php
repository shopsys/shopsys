<?php

declare(strict_types=1);


namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory as BasePromoCodeDataFactory;

class PromoCodeDataFactory extends BasePromoCodeDataFactory
{
    public function create(): BasePromoCodeData{
        return new PromoCodeData();
    }

    /**
     * @param BasePromoCode $promoCode
     * @return BasePromoCodeData
     */
    public function createFromPromoCode(BasePromoCode $promoCode): BasePromoCodeData{
        $promoCodeData = new PromoCodeData();
        $this->fillFromPromoCode($promoCodeData, $promoCode);

        return $promoCodeData;
    }

    /**
     * @param PromoCodeData $promoCodeData
     * @param PromoCode $promoCode
     */
    protected function fillFromPromoCode(BasePromoCodeData $promoCodeData, BasePromoCode $promoCode){
        parent::fillFromPromoCode($promoCodeData, $promoCode);
        $promoCodeData->domainId = $promoCode->getDomainId();
    }

}