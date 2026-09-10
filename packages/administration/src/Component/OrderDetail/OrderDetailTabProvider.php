<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail;

use Override;
use Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent\HistoryTabComponent;
use Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent\ItemsTabComponent;
use Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent\PaymentTransactionsTabComponent;
use Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent\WithdrawalTabComponent;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;

class OrderDetailTabProvider implements OrderDetailTabProviderInterface
{
    public const string HISTORY_TAB_ID = 'history';

    public function __construct(
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
    ) {
    }

    /**
     * @return iterable<\Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailTab>
     */
    #[Override]
    public function getTabs(): iterable
    {
        yield new OrderDetailTab(
            'withdrawal',
            t('Withdrawal'),
            WithdrawalTabComponent::COMPONENT_NAME,
            10,
            visibleWhen: fn (Order $order): bool => $this->withdrawalRequestFacade->findByOrder($order) !== null,
        );

        yield new OrderDetailTab(
            'items',
            t('Items'),
            ItemsTabComponent::COMPONENT_NAME,
            20,
        );

        yield new OrderDetailTab(
            'payment_transactions',
            t('Payments'),
            PaymentTransactionsTabComponent::COMPONENT_NAME,
            30,
            static fn (Order $order): bool => $order->getPaymentTransactionsCount() === 0 && $order->getRedeemedGiftVouchers() === [],
        );

        yield new OrderDetailTab(
            self::HISTORY_TAB_ID,
            t('History'),
            HistoryTabComponent::COMPONENT_NAME,
            40,
        );
    }
}
