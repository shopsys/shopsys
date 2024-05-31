<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Order\OrderData;

class OrderProcessingData
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput $orderInput
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    public function __construct(
        public readonly OrderInput $orderInput,
        public readonly OrderData $orderData,
    ) {
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->orderInput->getDomainConfig()->getId();
    }

    /**
     * @return string
     */
    public function getDomainLocale(): string
    {
        return $this->orderInput->getDomainConfig()->getLocale();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getDomainConfig(): DomainConfig
    {
        return $this->orderInput->getDomainConfig();
    }
}
