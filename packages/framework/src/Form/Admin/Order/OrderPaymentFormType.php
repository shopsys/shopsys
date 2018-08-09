<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderPaymentData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class OrderPaymentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('payment', ChoiceType::class, [
                'required' => true,
                'choices' => $options['payments'],
                'choice_label' => 'name',
                'choice_value' => 'id',
                'error_bubbling' => true,
            ])
            ->add('priceWithVat', MoneyType::class, [
                'currency' => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter unit price with VAT']),
                ],
                'error_bubbling' => true,
            ])
            ->add('vatPercent', MoneyType::class, [
                'currency' => false,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter VAT rate']),
                ],
                'error_bubbling' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('payments')
            ->setAllowedTypes('payments', 'array')
            ->setDefaults([
                'data_class' => OrderPaymentData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
