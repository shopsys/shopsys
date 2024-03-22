<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\OrderData;

/**
 * This class is only a temporary solution until Cart and Order is merged into one entity
 */
class OrderProcessingData
{
    public function __construct(
        public readonly Cart $cart,
        public readonly OrderData $orderData,
        public readonly DomainConfig $domainConfig,
    ) {
    }
}
