<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderData;

class OrderProcessingData
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput $orderInput
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(
        public readonly OrderInput $orderInput,
        public readonly OrderData $orderData,
        public readonly DomainConfig $domainConfig,
        public readonly ?CustomerUser $customerUser,
    ) {
    }
}
