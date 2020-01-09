<?php

declare(strict_types=1);


namespace App\Model\Order\PromoCode;


use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactory as BasePromoCodeFactory;

class PromoCodeFactory extends BasePromoCodeFactory
{
    /**
     * @param BasePromoCodeData $data
     * @return BasePromoCode
     */
    public function create(BasePromoCodeData $data): BasePromoCode{
        $classData = $this->entityNameResolver->resolve(PromoCode::class);
        return new $classData($data);
    }


}