<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository as BasePromoCodeRepository;

/**
 * @method \App\Model\Order\PromoCode\PromoCode|null findById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode getById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAll()
 * @method \App\Model\Order\PromoCode\PromoCode|null findByCodeAndDomainId(string $code, int $domainId)
 * @method \App\Model\Order\PromoCode\PromoCode[]|null findByMassBatchId(int $batchId)
 */
class PromoCodeRepository extends BasePromoCodeRepository
{
}
