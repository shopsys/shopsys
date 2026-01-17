<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;

class CreateOrderResult
{
    protected bool $orderCreated;

    public function __construct(
        protected readonly ?Order $order = null,
        protected readonly ?CartWithModificationsResult $cartWithModificationsResult = null,
    ) {
        $this->orderCreated = false;

        if ($this->order !== null) {
            $this->orderCreated = true;
        }
    }

    public function isOrderCreated(): bool
    {
        return $this->orderCreated;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function getCart(): ?CartWithModificationsResult
    {
        return $this->cartWithModificationsResult;
    }
}
