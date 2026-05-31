<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail;

use Override;
use Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent\WithdrawalTabComponent;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;

class OrderDetailTabProvider implements OrderDetailTabProviderInterface
{
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
    }
}
