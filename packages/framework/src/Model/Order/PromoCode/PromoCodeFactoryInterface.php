<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

interface PromoCodeFactoryInterface
{

    public function create(PromoCodeData $data): PromoCode;
}
