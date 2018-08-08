<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Exception;

class InvalidPromoCodeException extends Exception implements PromoCodeException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $invalidPromoCode, Exception $previous = null)
    {
        parent::__construct('Promo code "' . $invalidPromoCode . '" is not valid.', 0, $previous);
    }
}
