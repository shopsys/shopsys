<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Admin\OrderDetail;

use Shopsys\FrameworkBundle\Model\Order\Order;

interface OrderDetailTabProviderInterface
{
    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\Admin\OrderDetail\OrderDetailTab>
     */
    public function getTabs(Order $order): array;
}
