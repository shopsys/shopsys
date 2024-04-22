<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderData;

class OrderProcessor
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     */
    public function __construct(
        protected readonly OrderProcessingStack $orderProcessingStack,
    ) {
    }

    /**
     * @template T of \Shopsys\FrameworkBundle\Model\Order\OrderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput $orderInput
     * @param T $orderData
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return T
     */
    public function process(
        OrderInput $orderInput,
        OrderData $orderData,
        DomainConfig $domainConfig,
        ?CustomerUser $customerUser,
    ): OrderData {
        $orderData = clone $orderData;

        $orderProcessingData = new OrderProcessingData(
            $orderInput,
            $orderData,
            $domainConfig,
            $customerUser,
        );

        $this->orderProcessingStack->rewind();

        $orderProcessingData = $this->orderProcessingStack->processNext($orderProcessingData);

        return $orderProcessingData->orderData;
    }
}
