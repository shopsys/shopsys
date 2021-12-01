<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\Exception;

use App\Model\Order\PromoCode\PromoCode;
use Exception;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;

class PromoCodeWithoutRelationWithAnyProductFromCurrentCartException extends Exception implements PromoCodeException
{
    /**
     * @param \App\Model\Order\PromoCode\PromoCode $invalidPromoCode
     * @param \Exception|null $previous
     */
    public function __construct(PromoCode $invalidPromoCode, ?Exception $previous = null)
    {
        parent::__construct('Promo code "' . $invalidPromoCode->getCode() . '" is not valid from any product in cart.', 0, $previous);
    }
}
