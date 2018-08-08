<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

interface PromoCodeDataFactoryInterface
{
    public function create(): PromoCodeData;

    public function createFromPromoCode(PromoCode $promoCode): PromoCodeData;
}
