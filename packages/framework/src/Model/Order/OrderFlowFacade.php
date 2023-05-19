<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

class OrderFlowFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFlowFactoryInterface $orderFlowFactory
     */
    public function __construct(protected readonly OrderFlowFactoryInterface $orderFlowFactory)
    {
    }

    public function resetOrderForm()
    {
        $orderFlow = $this->orderFlowFactory->create();
        $orderFlow->reset();
    }
}
