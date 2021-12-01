<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderResolverMap as BaseOrderResolverMap;

class OrderResolverMap extends BaseOrderResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        $orderDataArray = parent::map();
        $orderDataArray['Order']['promoCode'] = function (Order $order) {
            return $order->getGtmCoupon();
        };

        return $orderDataArray;
    }
}
