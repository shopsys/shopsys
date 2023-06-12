<?php

declare(strict_types=1);

namespace App\Model\Order;

class OrderCreatedResult
{
    /**
     * @param \App\Model\Order\Order $order
     * @param bool $loginCustomer
     */
    public function __construct(private Order $order, private bool $loginCustomer)
    {
    }

    /**
     * @return \App\Model\Order\Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return bool
     */
    public function isLoginCustomer(): bool
    {
        return $this->loginCustomer;
    }
}
