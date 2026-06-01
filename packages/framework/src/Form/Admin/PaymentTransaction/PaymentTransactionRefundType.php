<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PaymentTransaction;

use Override;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class PaymentTransactionRefundType extends AbstractType
{
    public const string VALIDATION_GROUP_ONLINE_REFUND = 'onlineRefund';
    public const string VALIDATION_GROUP_MANUAL_CORRECTION = 'manualCorrection';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('refundAmount', MoneyType::class, [
                'label' => t('Refund amount'),
                'scale' => 6,
                'required' => false,
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter refund amount.',
                        groups: [self::VALIDATION_GROUP_ONLINE_REFUND],
                    ),
                ],
            ])
            ->add('refundedAmount', MoneyType::class, [
                'label' => t('Refunded amount'),
                'scale' => 6,
                'required' => false,
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter refunded amount.',
                        groups: [self::VALIDATION_GROUP_MANUAL_CORRECTION],
                    ),
                ],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => PaymentTransactionRefundData::class,
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
                'validation_groups' => ['Default'],
            ]);
    }
}
