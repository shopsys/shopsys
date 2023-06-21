<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;

class NotAvailableForCustomerUserPricingGroup extends Exception implements PromoCodeException
{
    /**
     * @param string $invalidPromoCode
     * @param int $pricingGroupId
     * @param \Exception|null $previous
     */
    public function __construct(string $invalidPromoCode, int $pricingGroupId, ?Exception $previous = null)
    {
        parent::__construct(
            t(
                'Promo code "%invalidPromoCode%" is not available for pricing group ID "%pricingGroupId%".',
                [
                    '%invalidPromoCode%' => $invalidPromoCode,
                    '%pricingGroupId%' => $pricingGroupId,
                ],
                'validators',
            ),
            0,
            $previous,
        );
    }
}
