<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;

/**
 * @ORM\Table(name="promo_codes",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="domain_code_unique", columns={
 *         "domain_id", "code"
 *     })}
 * )
 * @ORM\Entity
 * @method __construct(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method edit(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method setData(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 */
class PromoCode extends BasePromoCode
{
}
