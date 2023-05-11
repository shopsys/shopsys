<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderResolverMap extends ResolverMap
{
    protected Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
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
                'differentDeliveryAddress' => function (Order $order) {
                    return !$order->isDeliveryAddressSameAsBillingAddress();
                },
                'status' => function (Order $order) {
                    return $order->getStatus()->getName($this->domain->getLocale());
                },
                'totalPrice' => function (Order $order) {
                    return new Price($order->getTotalPriceWithoutVat(), $order->getTotalPriceWithVat());
                },
            ],
        ];
    }
}
