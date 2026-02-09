<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Exception;

class InvalidPromoCodeException extends PromoCodeException
{
    public function __construct(string $invalidPromoCode, ?Exception $previous = null)
    {
        parent::__construct('Promo code "' . $invalidPromoCode . '" is not valid.', 0, $previous);
    }
}
