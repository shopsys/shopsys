<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;

class OrderResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalFacade $withdrawalFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade $withdrawalRequestFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly WithdrawalFacade $withdrawalFacade,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
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
                'withdrawalInstructions' => function (Order $order) {
                    return $this->withdrawalFacade->getWithdrawalInstructions($order);
                },
                'canRequestWithdrawal' => function (Order $order) {
                    return $this->withdrawalFacade->canRequestWithdrawal($order);
                },
                'withdrawalDeadline' => function (Order $order) {
                    return $this->withdrawalFacade->getWithdrawalDeadline($order);
                },
                'withdrawalRequest' => function (Order $order) {
                    return $this->withdrawalRequestFacade->findByOrder($order);
                },
            ],
        ];
    }
}
