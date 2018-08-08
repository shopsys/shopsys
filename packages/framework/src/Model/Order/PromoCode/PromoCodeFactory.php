<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

class PromoCodeFactory implements PromoCodeFactoryInterface
{
    public function create(PromoCodeData $data): PromoCode
    {
        return new PromoCode($data);
    }
}
