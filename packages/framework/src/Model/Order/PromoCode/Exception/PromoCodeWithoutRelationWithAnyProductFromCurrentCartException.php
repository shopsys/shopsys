<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class PromoCodeWithoutRelationWithAnyProductFromCurrentCartException extends Exception implements PromoCodeException
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $invalidPromoCode
     * @param \Exception|null $previous
     */
    public function __construct(PromoCode $invalidPromoCode, ?Exception $previous = null)
    {
        parent::__construct(t('Promo code "%promoCode%" is not valid for any product in cart.', [
            '%promoCode%' => $invalidPromoCode->getCode(),
        ], 'validators'), 0, $previous);
    }
}
