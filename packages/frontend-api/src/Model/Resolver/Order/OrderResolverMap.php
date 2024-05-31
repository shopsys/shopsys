<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;

class OrderResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Order' => [
                'country' => function (Order $order) {
                    return $order->getCountry()->getCode();
                },
                'creationDate' => function (Order $order) {
                    return $order->getCreatedAt();
                },
                'deliveryCountry' => function (Order $order) {
                    return $order->getDeliveryCountry() === null ? '' : $order->getDeliveryCountry()->getCode();
                },
                'isDeliveryAddressDifferentFromBilling' => function (Order $order) {
                    return !$order->isDeliveryAddressSameAsBillingAddress();
                },
                'status' => function (Order $order) {
                    return $order->getStatus()->getName($this->domain->getLocale());
                },
            ],
        ];
    }
}
