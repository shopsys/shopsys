<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;

class OrderProcessor
{
    public function __construct(
        protected readonly OrderProcessingStack $orderProcessingStack,
        protected readonly OrderDataFactory $orderDataFactory,
    ) {

    }

    public function process(Cart $cart, DomainConfig $domainConfig): OrderData
    {
        $orderData = $this->orderDataFactory->create();

        $orderProcessingData = new OrderProcessingData(
            $cart,
            $orderData,
            $domainConfig,
        );

        try {
            $this->orderProcessingStack->next()->handle($orderProcessingData, $this->orderProcessingStack);
        } catch (NoMoreMiddlewareInStackException) {
            // pass
        }

        return $orderData;
    }

}
