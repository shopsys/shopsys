<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

class PromoCodeDataFactory implements PromoCodeDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData
     */
    protected function createInstance(): PromoCodeData
    {
        return new PromoCodeData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData
     */
    public function create(): PromoCodeData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData
     */
    public function createFromPromoCode(PromoCode $promoCode): PromoCodeData
    {
        $promoCodeData = $this->createInstance();
        $this->fillFromPromoCode($promoCodeData, $promoCode);

        return $promoCodeData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    protected function fillFromPromoCode(PromoCodeData $promoCodeData, PromoCode $promoCode)
    {
        $promoCodeData->code = $promoCode->getCode();
        $promoCodeData->percent = (float)$promoCode->getPercent();
    }
}
