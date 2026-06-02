<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent;

use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Order\OrderItemFormType;
use Shopsys\FrameworkBundle\Form\OrderItemsType;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: self::COMPONENT_NAME,
    template: '@ShopsysAdministration/content/order/detail/components/items_tab.html.twig',
)]
#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class ItemsTabComponent
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public const string COMPONENT_NAME = 'OrderDetail:ItemsTab';

    public const string ORDER_DETAIL_ITEMS_SAVED_BROWSER_EVENT = 'order-detail-items:saved';
    public const string ORDER_DETAIL_ITEMS_CANCELLED_BROWSER_EVENT = 'order-detail-items:cancelled';

    #[LiveProp]
    public int $orderId;

    #[LiveProp]
    public bool $editMode = false;

    protected ?OrderData $orderData = null;

    protected ?Order $order = null;

    protected int $newItemCounter = 0;

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly FlashMessageService $flashMessageService,
        protected readonly OrderItemFacade $orderItemFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly TransportFacade $transportFacade,
        protected readonly PaymentFacade $paymentFacade,
    ) {
    }

    public function getOrder(): Order
    {
        return $this->order ??= $this->orderFacade->getById($this->orderId);
    }

    #[LiveAction]
    #[CanEdit]
    public function switchToEdit(): void
    {
        $this->editMode = true;
    }

    #[LiveAction]
    #[CanEdit]
    public function addItem(): void
    {
        $this->editMode = true;
        $this->clearValidationState();

        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PRODUCT);
        $orderItemData->quantity = 1;

        $this->formValues['orderItems']['itemsWithoutTransportAndPayment'][$this->getNewItemKey()] = $this->extractFormValuesFromOrderItemData($orderItemData);
    }

    #[LiveAction]
    #[CanEdit]
    public function addProduct(#[LiveArg] int $productId): void
    {
        $this->editMode = true;
        $this->clearValidationState();

        $orderItemData = $this->orderItemFacade->createProductOrderItemData($this->getOrder(), $productId);
        $this->formValues['orderItems']['itemsWithoutTransportAndPayment'][$this->getNewItemKey()] = $this->extractFormValuesFromOrderItemData($orderItemData);
    }

    #[LiveAction]
    #[CanEdit]
    public function removeItem(#[LiveArg] string $itemIndex): void
    {
        $this->editMode = true;
        $this->clearValidationState();

        $items = $this->formValues['orderItems']['itemsWithoutTransportAndPayment'] ?? [];

        if (count($items) <= 1 || !array_key_exists($itemIndex, $items)) {
            return;
        }

        unset($this->formValues['orderItems']['itemsWithoutTransportAndPayment'][$itemIndex]);
    }

    #[LiveAction]
    #[CanEdit]
    public function prefillTransport(#[LiveArg] int $transportId): void
    {
        $order = $this->getOrder();
        $transportPricesWithVatByTransportId = $this->transportFacade->getTransportPricesWithVatByCurrencyAndDomainIdIndexedByTransportId(
            $order->getDomainId(),
        );
        $transportVatPercentsByTransportId = $this->transportFacade->getTransportVatPercentsByDomainIdIndexedByTransportId(
            $order->getDomainId(),
        );

        if (!array_key_exists($transportId, $transportPricesWithVatByTransportId)) {
            return;
        }

        $this->formValues['orderItems']['orderTransport']['unitPriceWithVat'] = $transportPricesWithVatByTransportId[$transportId]->getAmount();
        $this->formValues['orderItems']['orderTransport']['vatPercent'] = $transportVatPercentsByTransportId[$transportId] ?? '';
        $this->formValues['orderItems']['orderTransport']['setPricesManually'] = '1';
        $this->formValues['orderItems']['orderTransport']['unitPriceWithoutVat'] = '';
    }

    #[LiveAction]
    #[CanEdit]
    public function prefillPayment(#[LiveArg] int $paymentId): void
    {
        $order = $this->getOrder();
        $paymentPricesWithVatByPaymentId = $this->paymentFacade->getPaymentPricesWithVatByDomainIdIndexedByPaymentId(
            $order->getDomainId(),
            $order->getCurrencyRoundingType(),
            $order->getCurrencyRoundingPlacesPriceWithoutVat(),
        );
        $paymentVatPercentsByPaymentId = $this->paymentFacade->getPaymentVatPercentsByDomainIdIndexedByPaymentId(
            $order->getDomainId(),
        );

        if (!array_key_exists($paymentId, $paymentPricesWithVatByPaymentId)) {
            return;
        }

        $this->formValues['orderItems']['orderPayment']['unitPriceWithVat'] = $paymentPricesWithVatByPaymentId[$paymentId]->getAmount();
        $this->formValues['orderItems']['orderPayment']['vatPercent'] = $paymentVatPercentsByPaymentId[$paymentId] ?? '';
        $this->formValues['orderItems']['orderPayment']['setPricesManually'] = '1';
        $this->formValues['orderItems']['orderPayment']['unitPriceWithoutVat'] = '';
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
        $this->editMode = false;
        $this->resetFormFromOrder();

        $this->flashMessageService->addSuccessFlash(t('Order items have been saved.'));
        $this->emit(SectionEditorFormComponent::ORDER_DETAIL_SECTION_UPDATED_EVENT);
        $this->dispatchBrowserEvent(self::ORDER_DETAIL_ITEMS_SAVED_BROWSER_EVENT);
    }

    #[LiveAction]
    #[CanEdit]
    public function cancel(): void
    {
        $this->editMode = false;
        $this->resetFormFromOrder();
        $this->dispatchBrowserEvent(self::ORDER_DETAIL_ITEMS_CANCELLED_BROWSER_EVENT);
    }

    protected function instantiateForm(): FormInterface
    {
        $order = $this->getOrder();
        $this->orderData = $this->orderDataFactory->createFromOrder($order);

        return $this->formFactory
            ->createNamedBuilder('order_form', FormType::class, $this->orderData, [
                'data_class' => OrderData::class,
                'attr' => [
                    'novalidate' => 'novalidate',
                    'data-model' => $this->getDataModelValue(),
                ],
            ])
            ->add('orderItems', OrderItemsType::class, [
                'order' => $order,
            ])
            ->getForm();
    }

    protected function getDataModelValue(): ?string
    {
        return 'norender|*';
    }

    protected function resetFormFromOrder(): void
    {
        $this->order = null;
        $this->orderData = null;
        $this->resetForm();
    }

    protected function clearValidationState(): void
    {
        $this->isValidated = false;
        $this->validatedFields = [];
    }

    protected function getNewItemKey(): string
    {
        $items = $this->formValues['orderItems']['itemsWithoutTransportAndPayment'] ?? [];

        foreach (array_keys($items) as $itemKey) {
            if (!str_starts_with((string)$itemKey, OrderData::NEW_ITEM_PREFIX)) {
                continue;
            }

            $newItemNumber = (int)substr((string)$itemKey, strlen(OrderData::NEW_ITEM_PREFIX));
            $this->newItemCounter = max($this->newItemCounter, $newItemNumber + 1);
        }

        return OrderData::NEW_ITEM_PREFIX . $this->newItemCounter++;
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractFormValuesFromOrderItemData(OrderItemData $orderItemData): array
    {
        $orderItemForm = $this->formFactory->createNamed('orderItem', OrderItemFormType::class, $orderItemData, [
            'csrf_protection' => false,
        ]);

        return $this->extractFormValues($orderItemForm->createView());
    }
}
