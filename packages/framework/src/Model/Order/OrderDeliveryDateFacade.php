<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;

class OrderDeliveryDateFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Psr\Clock\ClockInterface $clock
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function setDeliveredNowIfNecessary(Order $order): void
    {
        if ($this->shouldSetDeliveredAt($order)) {
            $order->setDeliveredAt($this->clock->now());
            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return bool
     */
    protected function shouldSetDeliveredAt(Order $order): bool
    {
        return $order->getDeliveredAt() === null && ($order->getStatus()->getType() === OrderStatusTypeEnum::TYPE_DONE);
    }
}
