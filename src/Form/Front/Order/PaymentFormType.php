<?php

declare(strict_types = 1);

namespace App\Form\Front\Order;

use App\Model\GoPay\BankSwift\GoPayBankSwift;
use App\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Form\SingleCheckboxChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PaymentFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $payments = $options['payments'];
        $goPayBankSwifts = $options['goPayBankSwifts'];

        $builder
            ->add('payment', SingleCheckboxChoiceType::class, [
                'choices' => $payments,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'data_class' => Payment::class,
                'constraints' => [
                    new Constraints\NotNull(['message' => 'Vyberte prosím platbu']),
                ],
                'invalid_message' => 'Vyberte prosím platbu',
            ])
            ->add('goPayBankSwift', SingleCheckboxChoiceType::class, [
                'choices' => $goPayBankSwifts,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'data_class' => GoPayBankSwift::class,
            ]);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'payment_form';
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['payments', 'goPayBankSwifts'])
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
