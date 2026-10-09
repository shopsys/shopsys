<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class FreeTransportAndPaymentPromoCodeNotNeededException extends PromoCodeException
{
    public function __construct(PromoCode $promoCode, ?Exception $previous = null)
    {
        parent::__construct(t('Promo code "%promoCode%" is not needed, electronic gift vouchers in cart are delivered by email for free.', [
            '%promoCode%' => $promoCode->getCode(),
        ], 'validators'), 0, $previous);
    }
}
