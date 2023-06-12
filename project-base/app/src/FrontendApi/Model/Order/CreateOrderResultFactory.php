<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\FrontendApi\Model\Cart\CartWithModificationsResult;
use App\Model\Order\Order;

class CreateOrderResultFactory
{
    /**
     * @param \App\Model\Order\Order $order
     * @return \App\FrontendApi\Model\Order\CreateOrderResult
     */
    public function getCreateOrderResultByOrder(Order $order): CreateOrderResult
    {
        return new CreateOrderResult($order);
    }

    /**
     * @param \App\FrontendApi\Model\Cart\CartWithModificationsResult $cartWithModifications
     * @return \App\FrontendApi\Model\Order\CreateOrderResult
     */
    public function getCreateOrderResultByCartWithModifications(
        CartWithModificationsResult $cartWithModifications,
    ): CreateOrderResult {
        return new CreateOrderResult(null, $cartWithModifications);
    }
}
