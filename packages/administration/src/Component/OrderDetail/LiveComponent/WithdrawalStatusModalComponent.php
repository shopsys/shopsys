<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent;

use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderWithdrawalFormType;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestData;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: 'OrderDetail:WithdrawalStatusModal',
    template: '@ShopsysAdministration/content/order/detail/components/withdrawal_status_modal.html.twig',
)]
#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class WithdrawalStatusModalComponent
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public int $orderId;

    #[LiveProp]
    public int $statusId;

    protected ?WithdrawalRequestData $withdrawalRequestData = null;

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderStatusFacade $orderStatusFacade,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly WithdrawalRequestDataFactory $withdrawalRequestDataFactory,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly FlashMessageService $flashMessageService,
    ) {
    }

    #[LiveAction]
    #[CanEdit]
    public function save(): ?RedirectResponse
    {
        $this->submitForm();

        if ($this->withdrawalRequestData === null) {
            return null;
        }

        $order = $this->getOrder();
        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->status = $this->orderStatusFacade->getById($this->statusId);
        $orderData->withdrawalRequestData = $this->withdrawalRequestData;

        $this->orderFacade->edit($this->orderId, $orderData);
        $this->flashMessageService->addSuccessFlash(t('Order status has been changed.'));

        return new RedirectResponse($this->urlGenerator->generate('admin_order_edit', [
            'id' => $this->orderId,
        ]));
    }

    public function getOrder(): Order
    {
        return $this->orderFacade->getById($this->orderId);
    }

    protected function instantiateForm(): FormInterface
    {
        $order = $this->getOrder();
        $this->withdrawalRequestData = $this->withdrawalRequestDataFactory->createFromWithdrawalRequestOrPrefilledFromOrder(
            $order,
            $this->withdrawalRequestFacade->findByOrder($order),
        );

        return $this->formFactory->createNamed(
            'order_withdrawal_status',
            OrderWithdrawalFormType::class,
            $this->withdrawalRequestData,
            [
                'domain_id' => $order->getDomainId(),
                'validation_groups' => [
                    'Default',
                    OrderWithdrawalFormType::VALIDATION_GROUP_WITHDRAWAL_REQUIRED,
                ],
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
            ],
        );
    }
}
