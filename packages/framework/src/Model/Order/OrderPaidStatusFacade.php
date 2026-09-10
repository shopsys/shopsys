<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Messenger\OrderMarkedAsPaidMessageDispatcher;

class OrderPaidStatusFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderMarkedAsPaidMessageDispatcher $orderMarkedAsPaidMessageDispatcher,
    ) {
    }

    public function markOrderAsPaid(Order $order): void
    {
        if ($order->isPaid()) {
            return;
        }

        $order->markAsPaid(new DateTimeImmutable());
        $this->em->flush();

        $this->orderMarkedAsPaidMessageDispatcher->dispatchOrderMarkedAsPaidMessage($order->getId());
    }

    public function refreshOrderPaidStatusByPaymentTransactions(Order $order): void
    {
        if ($order->hasPaidPaymentTransaction()) {
            $this->markOrderAsPaid($order);
        }
    }
}
