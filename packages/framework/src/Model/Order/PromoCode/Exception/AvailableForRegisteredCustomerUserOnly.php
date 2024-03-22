<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Exception;

class AvailableForRegisteredCustomerUserOnly extends Exception implements PromoCodeException
{
    /**
     * @param string $invalidPromoCode
     * @param \Exception|null $previous
     */
    public function __construct(string $invalidPromoCode, ?Exception $previous = null)
    {
        parent::__construct(
            t('Promo code "%promoCode%" is available for registered customer user only.', [
                '%promoCode' => $invalidPromoCode,
            ], 'validators'),
            0,
            $previous,
        );
    }
}
