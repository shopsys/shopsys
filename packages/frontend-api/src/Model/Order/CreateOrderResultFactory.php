<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;

class CreateOrderResultFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResult
     */
    public function getCreateOrderResultByOrder(Order $order): CreateOrderResult
    {
        return new CreateOrderResult($order);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult $cartWithModifications
     * @return \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResult
     */
    public function getCreateOrderResultByCartWithModifications(
        CartWithModificationsResult $cartWithModifications,
    ): CreateOrderResult {
        return new CreateOrderResult(null, $cartWithModifications);
    }
}
