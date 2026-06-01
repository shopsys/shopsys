<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction\PaymentTransactionRefundType;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\PaymentTransactionNotRefundableException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\PaymentTransactionRefundFailedException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\RefundAmountGreaterThanRefundableAmountException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\RefundAmountNotPositiveException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\RefundedAmountGreaterThanPaidAmountException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\RefundedAmountNegativeException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundFacade;
use Shopsys\FrameworkBundle\Twig\PriceExtension;
use Symfony\Component\Form\FormError;
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
    template: '@ShopsysAdministration/content/order/detail/components/payment_transactions_tab.html.twig',
)]
#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class PaymentTransactionsTabComponent
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    public const string COMPONENT_NAME = 'OrderDetail:PaymentTransactionsTab';

    #[LiveProp]
    public int $orderId;

    #[LiveProp]
    public ?int $refundModalPaymentTransactionId = null;

    #[LiveProp]
    public ?string $refundModalErrorMessage = null;

    protected ?PaymentTransactionRefundData $paymentTransactionRefundData = null;

    protected ?Order $order = null;

    protected ?string $refundActionValidationGroup = null;

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory,
        protected readonly PaymentTransactionRefundFacade $paymentTransactionRefundFacade,
        protected readonly FlashMessageService $flashMessageService,
        protected readonly PriceExtension $priceExtension,
    ) {
    }

    public function getOrder(): Order
    {
        return $this->order ??= $this->orderFacade->getById($this->orderId);
    }

    #[LiveAction]
    #[CanEdit]
    public function openRefundModal(#[LiveArg] int $paymentTransactionId): void
    {
        $paymentTransaction = $this->getPaymentTransaction($paymentTransactionId);
        $this->refundModalPaymentTransactionId = $paymentTransactionId;
        $this->paymentTransactionRefundData = $this->paymentTransactionRefundDataFactory->createFromPaymentTransaction($paymentTransaction);
        $this->refundModalErrorMessage = null;

        $this->resetForm();
    }

    #[LiveAction]
    #[CanEdit]
    public function closeRefundModal(): void
    {
        $this->refundModalPaymentTransactionId = null;
        $this->paymentTransactionRefundData = null;
        $this->refundModalErrorMessage = null;
        $this->refundActionValidationGroup = null;

        $this->resetForm();
    }

    #[LiveAction]
    #[CanEdit]
    public function saveManualRefundedAmount(): void
    {
        $this->refundModalErrorMessage = null;
        $paymentTransaction = $this->getRefundModalPaymentTransaction();

        $this->submitFormField('refundedAmount', PaymentTransactionRefundType::VALIDATION_GROUP_MANUAL_CORRECTION);

        if ($this->paymentTransactionRefundData?->refundedAmount === null) {
            return;
        }

        try {
            $this->paymentTransactionRefundFacade->changeManualRefundedAmount($paymentTransaction, $this->paymentTransactionRefundData->refundedAmount);
            $this->flashMessageService->addSuccessFlash(t('Refunded amount has been changed.'));
            $this->emit(SectionEditorFormComponent::ORDER_DETAIL_SECTION_UPDATED_EVENT);
            $this->closeRefundModal();
        } catch (RefundedAmountGreaterThanPaidAmountException) {
            $this->addFormError(
                'refundedAmount',
                t(
                    'You can set refunded amount only up to %paidAmount%.',
                    [
                        '%paidAmount%' => $this->priceExtension->priceWithCurrencyByOrderFilter($paymentTransaction->getPaidAmount(), $this->getOrder()),
                    ],
                    Translator::VALIDATOR_TRANSLATION_DOMAIN,
                ),
            );
        } catch (RefundedAmountNegativeException) {
            $this->addFormError(
                'refundedAmount',
                t('Refunded amount cannot be negative.', domain: Translator::VALIDATOR_TRANSLATION_DOMAIN),
            );
        }
    }

    #[LiveAction]
    #[CanEdit]
    public function executeOnlineRefund(): void
    {
        $this->refundModalErrorMessage = null;
        $paymentTransaction = $this->getRefundModalPaymentTransaction();

        $this->submitFormField('refundAmount', PaymentTransactionRefundType::VALIDATION_GROUP_ONLINE_REFUND);

        if ($this->paymentTransactionRefundData?->refundAmount === null) {
            return;
        }

        try {
            if ($this->paymentTransactionRefundFacade->executeOnlineRefund($paymentTransaction, $this->paymentTransactionRefundData->refundAmount)) {
                $this->paymentTransactionRefundData->refundAmount = null;
                $this->resetForm();
                $this->flashMessageService->addSuccessFlash(t('Refund has been sent.'));
                $this->emit(SectionEditorFormComponent::ORDER_DETAIL_SECTION_UPDATED_EVENT);
                $this->closeRefundModal();

                return;
            }

            $this->refundModalErrorMessage = t('Refund was not sent. Please check the payment transaction state.');
        } catch (PaymentTransactionRefundFailedException) {
            $this->refundModalErrorMessage = t('GoPay API return error - go to GoPay admin and find transaction %paymentId% and check if is all right.', [
                '%paymentId%' => $paymentTransaction->getExternalPaymentIdentifier(),
            ]);
        } catch (RefundAmountGreaterThanRefundableAmountException) {
            $this->addFormError(
                'refundAmount',
                t(
                    'You can refund only %refundableAmount%.',
                    [
                        '%refundableAmount%' => $this->priceExtension->priceWithCurrencyByOrderFilter($paymentTransaction->getRefundableAmount(), $this->getOrder()),
                    ],
                    Translator::VALIDATOR_TRANSLATION_DOMAIN,
                ),
            );
        } catch (PaymentTransactionNotRefundableException) {
            $this->addFormError(
                'refundAmount',
                t('Payment transaction is not refundable.', domain: Translator::VALIDATOR_TRANSLATION_DOMAIN),
            );
        } catch (RefundAmountNotPositiveException) {
            $this->addFormError(
                'refundAmount',
                t('Refund amount must be greater than zero.', domain: Translator::VALIDATOR_TRANSLATION_DOMAIN),
            );
        }
    }

    public function getRefundModalPaymentTransaction(): PaymentTransaction
    {
        if ($this->refundModalPaymentTransactionId === null) {
            throw new InvalidArgumentException('Payment transaction refund modal is not open.');
        }

        return $this->getPaymentTransaction($this->refundModalPaymentTransactionId);
    }

    protected function instantiateForm(): FormInterface
    {
        if ($this->refundModalPaymentTransactionId === null) {
            return $this->formFactory->createNamed('payment_transaction_refund');
        }

        $paymentTransaction = $this->getRefundModalPaymentTransaction();
        $this->paymentTransactionRefundData ??= $this->paymentTransactionRefundDataFactory->createFromPaymentTransaction($paymentTransaction);

        return $this->formFactory->createNamed(
            'payment_transaction_refund',
            PaymentTransactionRefundType::class,
            $this->paymentTransactionRefundData,
            [
                'validation_groups' => $this->getValidationGroups(),
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
            ],
        );
    }

    protected function submitFormField(string $fieldName, string $validationGroup): void
    {
        $this->refundActionValidationGroup = $validationGroup;
        $this->validatedFields = [sprintf('payment_transaction_refund.%s', $fieldName)];
        $this->submitForm(false);
    }

    protected function addFormError(string $fieldName, string $message): void
    {
        $this->getForm()->get($fieldName)->addError(new FormError($message));
        $this->validatedFields = [sprintf('payment_transaction_refund.%s', $fieldName)];
        $this->formView = null;
    }

    /**
     * @return list<string>
     */
    protected function getValidationGroups(): array
    {
        $validationGroups = ['Default'];

        if ($this->refundActionValidationGroup !== null) {
            $validationGroups[] = $this->refundActionValidationGroup;
        }

        return $validationGroups;
    }

    protected function getPaymentTransaction(int $paymentTransactionId): PaymentTransaction
    {
        foreach ($this->getOrder()->getPaymentTransactions() as $paymentTransaction) {
            if ($paymentTransaction->getId() === $paymentTransactionId) {
                return $paymentTransaction;
            }
        }

        throw new InvalidArgumentException('Payment transaction was not found in this order.');
    }
}
