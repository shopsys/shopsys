<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Exception;

class AlreadyAppliedPromoCodeException extends PromoCodeException
{
    public function __construct(string $promoCode, ?Exception $previous = null)
    {
        parent::__construct(t('Promo code "%promoCode%" is already applied.', [
            '%promoCode%' => $promoCode,
        ], 'validators'), 0, $previous);
    }
}
