<?php

namespace Shopsys\FrameworkBundle\Model\Order;

interface OrderNumberSequenceFactoryInterface
{
    public function create(int $id, string $number): OrderNumberSequence;
}
