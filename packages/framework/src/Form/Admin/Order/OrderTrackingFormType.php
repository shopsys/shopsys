<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

final class OrderTrackingFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('trackingNumber', TextType::class, [
                'label' => 'Tracking number',
                'required' => false,
                'constraints' => [
                    new Length(max: 100),
                ],
            ])
            ->add('deliveredAt', DateTimeType::class, [
                'label' => 'Delivered at',
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }
}
