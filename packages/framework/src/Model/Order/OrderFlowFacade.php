<?php

namespace Shopsys\FrameworkBundle\Model\Order;

class OrderFlowFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFlowFactoryInterface
     */
    protected $orderFlowFactory;

    public function __construct(OrderFlowFactoryInterface $orderFlowFactory)
    {
        $this->orderFlowFactory = $orderFlowFactory;
    }

    public function resetOrderForm(): void
    {
        $orderFlow = $this->orderFlowFactory->create();
        $orderFlow->reset();
    }
}
