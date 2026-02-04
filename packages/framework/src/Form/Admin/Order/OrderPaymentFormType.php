<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\CopyTotalPricesOfOrderItemTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\InverseTransformer;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class OrderPaymentFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('payment', ChoiceType::class, [
                'required' => true,
                'choices' => $options['payments'],
                'choice_label' => 'name',
                'choice_value' => 'id',
            ])
            ->add('unitPriceWithVat', MoneyType::class, [
                'scale' => 6,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter unit price with VAT'),
                ],
            ])
            ->add('vatPercent', NumberType::class, [
                'input' => 'string',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter unit price without VAT'),
                ],
            ])
            ->add('unitPriceWithoutVat', MoneyType::class, [
                'scale' => 6,
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter price',
                        groups: [OrderItemFormType::VALIDATION_GROUP_NOT_USING_PRICE_CALCULATION],
                    ),
                ],
            ])
            ->add(
                $builder->create('setPricesManually', CheckboxType::class, [
                    'property_path' => 'usePriceCalculation',
                    'required' => false,
                    'label' => false,
                ])->addModelTransformer(new InverseTransformer()),
            )
            ->addModelTransformer(new CopyTotalPricesOfOrderItemTransformer());
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('payments')
            ->setAllowedTypes('payments', 'array')
            ->setDefaults([
                'data_class' => OrderItemData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'validation_groups' => function (FormInterface $form) {
                    return OrderItemFormType::resolveValidationGroups($form);
                },
            ]);
    }
}
