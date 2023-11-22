<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade as BaseOrderFlowFacade;

class OrderFlowFacade extends BaseOrderFlowFacade
{
    public function __construct()
    {
    }

    /**
     * @deprecated - Twig storefront is not used anymore
     */
    public function resetOrderForm(): void
    {
    }
}
