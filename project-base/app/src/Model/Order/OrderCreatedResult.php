<?php

declare(strict_types=1);

namespace App\Model\Order;

class OrderCreatedResult
{
    /**
     * @var \App\Model\Order\Order
     */
    private Order $order;

    /**
     * @var bool
     */
    private bool $loginCustomer;

    /**
     * @param \App\Model\Order\Order $order
     * @param bool $loginCustomer
     */
    public function __construct(Order $order, bool $loginCustomer)
    {
        $this->order = $order;
        $this->loginCustomer = $loginCustomer;
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
