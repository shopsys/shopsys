<?php

namespace Shopsys\FrameworkBundle\Model\Order;

class OrderEditResult
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    private $orderItemsToCreate;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    private $orderItemsToDelete;

    /**
     * @var bool
     */
    private $statusChanged;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $orderItemsToCreate
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $orderItemsToDelete
     */
    public function __construct(array $orderItemsToCreate, array $orderItemsToDelete, $statusChanged)
    {
        $this->orderItemsToCreate = $orderItemsToCreate;
        $this->orderItemsToDelete = $orderItemsToDelete;
        $this->statusChanged = $statusChanged;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getOrderItemsToCreate(): array
    {
        return $this->orderItemsToCreate;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getOrderItemsToDelete(): array
    {
        return $this->orderItemsToDelete;
    }

    public function isStatusChanged(): bool
    {
        return $this->statusChanged;
    }
}
