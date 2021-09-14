<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;

class NotAvailableForCustomerUserPricingGroup extends Exception implements PromoCodeException
{
    /**
     * @param string $invalidPromoCode
     * @param int $customerUserId
     * @param \Exception|null $previous
     */
    public function __construct(string $invalidPromoCode, int $customerUserId, ?Exception $previous = null)
    {
        parent::__construct(
            sprintf(
                'Promo code "%s" is not available for pricing group of customer user with ID "%d".',
                $invalidPromoCode,
                $customerUserId
            ),
            0,
            $previous
        );
    }
}
