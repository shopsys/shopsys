<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status\Exception;

use Exception;

class InvalidOrderStatusTypeException extends Exception implements OrderStatusException
{
    /**
     * @var int
     */
    private $orderStatusType;

    /**
     * @param \Exception|null $previous
     */
    public function __construct(int $orderStatusType, Exception $previous = null)
    {
        $this->orderStatusType = $orderStatusType;
        parent::__construct('Order status type ' . $orderStatusType . ' is not valid', 0, $previous);
    }

    public function getOrderStatusType(): int
    {
        return $this->orderStatusType;
    }
}
