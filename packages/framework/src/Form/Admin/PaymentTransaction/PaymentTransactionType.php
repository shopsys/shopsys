<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction;

use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData;
use Shopsys\FrameworkBundle\Twig\PriceExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PaymentTransactionType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade $paymentTransactionFacade
     * @param \Shopsys\FrameworkBundle\Twig\PriceExtension $priceExtension
     */
    public function __construct(
        private readonly PaymentTransactionFacade $paymentTransactionFacade,
        private readonly PriceExtension $priceExtension,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('refundAmount', MoneyType::class, [
                'scale' => 6,
                'required' => false,
            ])
            ->add('refundedAmount', MoneyType::class, [
                'scale' => 6,
                'required' => false,
            ])
            ->add('executeRefund', HiddenType::class)
            ->add('sendRefund', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentTransactionRefundData::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
            'constraints' => [
                new Constraints\Callback([
                    'callback' => [$this, 'maximalRefundAmountValidation'],
                ]),
            ],
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData $paymentTransactionRefundData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function maximalRefundAmountValidation(
        PaymentTransactionRefundData $paymentTransactionRefundData,
        ExecutionContextInterface $context,
    ): void {
        if ($paymentTransactionRefundData->executeRefund === null) {
            return;
        }

        if ($paymentTransactionRefundData->refundAmount === null) {
            $context->buildViolation(t('If you want execute refund, you have to setup refund amount.'))
                ->atPath('refundAmount')
                ->addViolation();

            return;
        }

        /** @var \Symfony\Component\Form\Form $currentFormRow */
        $currentFormRow = $context->getObject();
        $originalPaymentTransaction = $this->paymentTransactionFacade->getById((int)$currentFormRow->getName());

        if (!$originalPaymentTransaction->getRefundableAmount()->isLessThan($paymentTransactionRefundData->refundAmount)) {
            return;
        }

        /** @var \Shopsys\FrameworkBundle\Model\Order\Order $order */
        $order = $currentFormRow->getParent()?->getConfig()->getOption('order');

        if ($order === null) {
            return;
        }
        $formattedRefundableAmount = $this->priceExtension->priceWithCurrencyFilter($originalPaymentTransaction->getRefundableAmount(), $order->getCurrency());
        $context->buildViolation(t('You can refund only %refundableAmount%.', ['%refundableAmount%' => $formattedRefundableAmount]))
            ->atPath('refundAmount')
            ->addViolation();
    }
}
