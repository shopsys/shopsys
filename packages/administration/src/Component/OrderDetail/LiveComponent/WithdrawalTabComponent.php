<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent;

use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: self::COMPONENT_NAME,
    template: '@ShopsysAdministration/content/order/detail/components/withdrawal_tab.html.twig',
)]
#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class WithdrawalTabComponent
{
    use DefaultActionTrait;

    public const string COMPONENT_NAME = 'OrderDetail:WithdrawalTab';

    #[LiveProp]
    public int $orderId;

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
    ) {
    }

    public function getWithdrawalRequest(): ?WithdrawalRequest
    {
        return $this->withdrawalRequestFacade->findByOrder($this->orderFacade->getById($this->orderId));
    }

    #[LiveListener(SectionEditorFormComponent::ORDER_DETAIL_SECTION_UPDATED_EVENT)]
    #[CanView]
    public function refresh(): void
    {
        // Intentionally empty; Live Component re-renders after listener invocation.
    }
}
