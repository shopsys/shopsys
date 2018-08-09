<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

interface OrderTransportDataFactoryInterface
{
    public function create(): OrderTransportData;

    public function createFromOrderTransport(OrderTransport $orderTransport): OrderTransportData;
}
