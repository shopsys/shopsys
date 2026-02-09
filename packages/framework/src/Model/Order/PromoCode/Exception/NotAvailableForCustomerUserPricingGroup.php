<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception;

use Exception;

class NotAvailableForCustomerUserPricingGroup extends PromoCodeException
{
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
