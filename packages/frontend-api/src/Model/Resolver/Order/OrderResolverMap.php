<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;

class OrderResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Overblog\DataLoader\DataLoaderInterface $orderItemsBatchLoader
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DataLoaderInterface $orderItemsBatchLoader,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Order' => [
                'creationDate' => function (Order $order) {
                    return $order->getCreatedAt();
                },
                'isDeliveryAddressDifferentFromBilling' => function (Order $order) {
                    return !$order->isDeliveryAddressSameAsBillingAddress();
                },
                'status' => function (Order $order) {
                    return $order->getStatus()->getName($this->domain->getLocale());
                },
                'promoCode' => function (Order $order) {
                    return $order->getGtmCoupon();
                },
                'items' => function (Order $order) {
                    return $this->orderItemsBatchLoader->load($order);
                },
            ],
        ];
    }
}
