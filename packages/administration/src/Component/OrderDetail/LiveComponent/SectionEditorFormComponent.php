<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent;

use Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSection;
use Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSectionRegistry;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderFormType;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: 'OrderDetail:SectionEditorForm',
    template: '@ShopsysAdministration/content/order/detail/components/section_editor_form.html.twig',
)]
#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class SectionEditorFormComponent
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public const string ORDER_DETAIL_SECTION_UPDATED_EVENT = 'orderDetailSectionUpdatedEvent';

    #[LiveProp]
    public int $orderId;

    #[LiveProp]
    public string $sectionId;

    protected ?OrderData $orderData = null;

    protected ?OrderDetailSection $resolvedSection = null;

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly FlashMessageService $flashMessageService,
        protected readonly OrderDetailSectionRegistry $orderDetailSectionRegistry,
    ) {
    }

    #[LiveAction]
    #[CanEdit]
    public function save(): void
    {
        $this->submitForm();

        if ($this->orderData === null) {
            return;
        }

        $this->orderFacade->edit($this->orderId, $this->orderData);
        $this->flashMessageService->addSuccessFlash($this->getSection()->getSuccessMessage());
        $this->emit(self::ORDER_DETAIL_SECTION_UPDATED_EVENT);
        $this->emit(SectionEditorComponent::SECTION_EDITOR_CLOSE_EVENT);
    }

    public function getOrder(): Order
    {
        return $this->orderFacade->getById($this->orderId);
    }

    public function getFormTemplate(): string
    {
        return $this->getSection()->getFormTemplate();
    }

    public function getSection(): OrderDetailSection
    {
        return $this->resolvedSection ??= $this->orderDetailSectionRegistry->getSection($this->sectionId);
    }

    protected function instantiateForm(): FormInterface
    {
        $section = $this->getSection();

        $order = $this->getOrder();
        $this->orderData = $this->orderDataFactory->createFromOrder($order);

        return $this->formFactory->createNamed(
            'order_edit_' . $section->getId(),
            OrderFormType::class,
            $this->orderData,
            [
                'order' => $order,
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
            ],
        );
    }

    protected function getDataModelValue(): ?string
    {
        return 'norender|*';
    }
}
