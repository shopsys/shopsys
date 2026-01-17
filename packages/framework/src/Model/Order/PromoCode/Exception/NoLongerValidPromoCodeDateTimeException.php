<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Exception;

class NoLongerValidPromoCodeDateTimeException extends PromoCodeException
{
    public function __construct(string $invalidPromoCode, ?Exception $previous = null)
    {
        parent::__construct(t('Promo code "%promoCode%" is no longer valid.', [
            '%promoCode%' => $invalidPromoCode,
        ], 'validators'), 0, $previous);
    }
}
