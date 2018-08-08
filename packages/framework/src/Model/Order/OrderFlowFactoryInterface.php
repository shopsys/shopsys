<?php

namespace Shopsys\FrameworkBundle\Model\Order;

interface OrderFlowFactoryInterface
{
    public function create(): \Craue\FormFlowBundle\Form\FormFlow;
}
