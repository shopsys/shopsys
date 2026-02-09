<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Exception;

use Exception;

class NoProductPriceForPricingGroupException extends Exception
{
    public function __construct(int $productId, int $pricingGroupId)
    {
        $message = sprintf(
            'There is no price for product ID "%d" and pricing group ID "%d".',
            $productId,
            $pricingGroupId,
        );

        parent::__construct($message);
    }
}
