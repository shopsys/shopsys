<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;

class OrderResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly OrderFacade $orderFacade,
    ) {
    }

    /**
     * @return array
     */
    #[Override]
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
                'statusType' => function (Order $order) {
                    return $order->getStatus()->getType();
                },
                'items' => function (Order $order) {
                    return $order->getItemsSortedWithRelatedItems();
                },
                'isPaid' => function (Order $order) {
                    return $this->orderFacade->isPaid($order);
                },
                'hasPaymentInProcess' => function (Order $order) {
                    return $this->orderFacade->hasPaymentInProcess($order);
                },
            ],
        ];
    }
}
