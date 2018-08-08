<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

interface OrderPaymentDataFactoryInterface
{
    public function create(): OrderPaymentData;

    public function createFromOrderPayment(OrderPayment $orderPayment): OrderPaymentData;
}
