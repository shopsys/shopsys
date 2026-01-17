<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;

class CreateOrderResultFactory
{
    public function getCreateOrderResultByOrder(Order $order): CreateOrderResult
    {
        return new CreateOrderResult($order);
    }

    public function getCreateOrderResultByCartWithModifications(
        CartWithModificationsResult $cartWithModifications,
    ): CreateOrderResult {
        return new CreateOrderResult(null, $cartWithModifications);
    }
}
