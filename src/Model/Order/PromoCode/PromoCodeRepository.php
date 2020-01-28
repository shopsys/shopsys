<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository as BasePromoCodeRepository;

/**
 * @method \App\Model\Order\PromoCode\PromoCode|null findById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode|null findByCode(string $code)
 * @method \App\Model\Order\PromoCode\PromoCode getById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAll()
 */
class PromoCodeRepository extends BasePromoCodeRepository
{
    /**
     * @param string $code
     * @param int $domainId
     * @return \App\Model\Order\PromoCode\PromoCode|null
     */
    public function findByCodeAndDomainId(string $code, int $domainId)
    {
        return $this->getPromoCodeRepository()->findOneBy(['code' => $code, 'domainId' => $domainId]);
    }
}
