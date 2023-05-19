<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

interface OrderFlowFactoryInterface
{
    /**
     * @return \Craue\FormFlowBundle\Form\FormFlow
     */
    public function create();
}
