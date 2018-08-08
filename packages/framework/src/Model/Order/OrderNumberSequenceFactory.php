<?php

namespace Shopsys\FrameworkBundle\Model\Order;

class OrderNumberSequenceFactory implements OrderNumberSequenceFactoryInterface
{
    public function create(int $id, string $number): OrderNumberSequence
    {
        return new OrderNumberSequence($id, $number);
    }
}
