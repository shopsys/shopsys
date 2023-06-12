<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use App\Model\Order\Order;

class CreateOrderResult
{
    private bool $orderCreated;

    /**
     * @param \App\Model\Order\Order|null $order
     * @param \App\FrontendApi\Model\Cart\CartWithModificationsResult|null $cartWithModificationsResult
     */
    public function __construct(
        private readonly ?Order $order = null,
        private readonly ?CartWithModificationsResult $cartWithModificationsResult = null,
    ) {
        $this->orderCreated = false;
        if ($this->order !== null) {
            $this->orderCreated = true;
        }
    }

    /**
     * @return bool
     */
    public function isOrderCreated(): bool
    {
        return $this->orderCreated;
    }

    /**
     * @return \App\Model\Order\Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult|null
     */
    public function getCart(): ?CartWithModificationsResult
    {
        return $this->cartWithModificationsResult;
    }
}
