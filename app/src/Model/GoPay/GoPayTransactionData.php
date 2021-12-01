<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\Order\Order;

class GoPayTransactionData
{
    /**
     * @var string
     */
    public $goPayId;

    /**
     * @var string|null
     */
    public $goPayStatus;

    /**
     * @var \App\Model\Order\Order
     */
    public $order;

    /**
     * @param string $goPayId
     * @param \App\Model\Order\Order $order
     * @param string|null $goPayStatus
     */
    public function __construct(string $goPayId, Order $order, ?string $goPayStatus = null)
    {
        $this->goPayId = $goPayId;
        $this->goPayStatus = $goPayStatus;
        $this->order = $order;
    }
}
