<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Order\OrderData;

class OrderProcessingData
{
    public function __construct(
        public readonly OrderInput $orderInput,
        public readonly OrderData $orderData,
    ) {
    }

    public function getDomainId(): int
    {
        return $this->orderInput->getDomainConfig()->getId();
    }

    public function getDomainLocale(): string
    {
        return $this->orderInput->getDomainConfig()->getLocale();
    }

    public function getDomainConfig(): DomainConfig
    {
        return $this->orderInput->getDomainConfig();
    }
}
