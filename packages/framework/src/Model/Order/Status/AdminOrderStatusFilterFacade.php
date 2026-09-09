<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminOrderStatusFilterFacade
{
    protected const string SESSION_KEY = 'admin_order_status_filter';

    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly OrderStatusRepository $orderStatusRepository,
    ) {
    }

    public function setSelectedOrderStatusId(?int $orderStatusId): void
    {
        $this->requestStack->getSession()->set(static::SESSION_KEY, $orderStatusId);
    }

    public function getSelectedOrderStatusId(): ?int
    {
        return $this->getSelectedOrderStatus()?->getId();
    }

    public function getSelectedOrderStatus(): ?OrderStatus
    {
        try {
            $orderStatusId = $this->requestStack->getSession()->get(static::SESSION_KEY);
        } catch (SessionNotFoundException) {
            return null;
        }

        if ($orderStatusId === null) {
            return null;
        }

        $orderStatus = $this->orderStatusRepository->findById((int)$orderStatusId);

        if ($orderStatus === null) {
            $this->setSelectedOrderStatusId(null);

            return null;
        }

        return $orderStatus;
    }
}
