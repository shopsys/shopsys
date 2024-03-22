<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;

class CreateOrderResult
{
    protected bool $orderCreated;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order|null $order
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult|null $cartWithModificationsResult
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
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult|null
     */
    public function getCart(): ?CartWithModificationsResult
    {
        return $this->cartWithModificationsResult;
    }
}
