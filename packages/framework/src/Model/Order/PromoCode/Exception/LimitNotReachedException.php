<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class LimitNotReachedException extends Exception implements PromoCodeException
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param \Exception|null $previous
     */
    public function __construct(PromoCode $promoCode, ?Exception $previous = null)
    {
        parent::__construct(t('Promo code "%promoCode%" is not applicable with current cart total price.', [
            '%promoCode%' => $promoCode->getCode(),
        ], 'validators'), 0, $previous);
    }
}
