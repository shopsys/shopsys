<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;

class AlreadyAppliedPromoCodeException extends Exception implements PromoCodeException
{
    /**
     * @param string $promoCode
     * @param \Exception|null $previous
     */
    public function __construct(string $promoCode, ?Exception $previous = null)
    {
        parent::__construct(t('Promo code "%promoCode%" is already applied.', [
            '%promoCode%' => $promoCode,
        ], 'validators'), 0, $previous);
    }
}
