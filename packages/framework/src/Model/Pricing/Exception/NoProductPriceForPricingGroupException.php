<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Exception;

use Exception;

class NoProductPriceForPricingGroupException extends Exception implements PricingException
{
    /**
     * @param int $productId
     * @param int $pricingGroupId
     */
    public function __construct(int $productId, int $pricingGroupId)
    {
        $message = sprintf(
            'There is no price for product ID "%d" and pricing group ID "%d".',
            $productId,
            $pricingGroupId
        );

        parent::__construct($message);
    }
}
