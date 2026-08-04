<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalSettingFacade;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;

class OrderResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly WithdrawalSettingFacade $withdrawalSettingFacade,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
        protected readonly ProductReviewEnabledChecker $productReviewEnabledChecker,
    ) {
    }

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
                'productReviewsAllowed' => function (Order $order) {
                    return $this->productReviewEnabledChecker->isEnabledForDomain($order->getDomainId())
                        && $order->getStatus()->areProductReviewsAllowed();
                },
                'items' => function (Order $order) {
                    return $order->getItemsSortedWithRelatedItems();
                },
                'withdrawalInstructions' => function (Order $order) {
                    return $this->withdrawalSettingFacade->getWithdrawalInstructions($order);
                },
                'canRequestWithdrawal' => function (Order $order) {
                    return $this->withdrawalRequestFacade->canRequestWithdrawal($order);
                },
                'withdrawalDeadline' => function (Order $order) {
                    return $this->withdrawalSettingFacade->getWithdrawalDeadline($order);
                },
                'withdrawalRequest' => function (Order $order) {
                    return $this->withdrawalRequestFacade->findByOrder($order);
                },
            ],
        ];
    }
}
