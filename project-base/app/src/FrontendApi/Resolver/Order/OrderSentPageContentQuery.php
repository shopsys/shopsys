<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

final class OrderSentPageContentQuery extends AbstractQuery
{
    /**
     * @param \App\FrontendApi\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        private readonly OrderFacade $orderFacade,
    ) {
    }

    /**
     * @param string $orderUuid
     * @return string
     */
    public function orderSentPageContentQuery(string $orderUuid): string
    {
        return $this->orderFacade->getOrderSentPageContent($orderUuid);
    }
}
