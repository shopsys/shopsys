<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\Model\Order\Order;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderResolverMap as BaseOrderResolverMap;

class OrderResolverMap extends BaseOrderResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Overblog\DataLoader\DataLoaderInterface $orderItemsBatchLoader
     */
    public function __construct(Domain $domain, private DataLoaderInterface $orderItemsBatchLoader)
    {
        parent::__construct($domain);
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        $orderDataArray = parent::map();
        $orderDataArray['Order']['promoCode'] = function (Order $order) {
            return $order->getGtmCoupon();
        };
        $orderDataArray['Order']['country'] = function (Order $order) {
            return $order->getCountry();
        };
        $orderDataArray['Order']['deliveryCountry'] = function (Order $order) {
            return $order->getDeliveryCountry();
        };
        $orderDataArray['Order']['items'] = function (Order $order) {
            return $this->orderItemsBatchLoader->load($order);
        };

        return $orderDataArray;
    }
}
