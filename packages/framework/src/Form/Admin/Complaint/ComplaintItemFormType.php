<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Complaint;

use Override;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ComplaintItemFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productName', TextType::class, [
                'disabled' => true,
            ])
            ->add('catnum', TextType::class, [
                'disabled' => true,
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter description'),
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter quantity'),
                    new Constraints\GreaterThan(
                        value: 0,
                        message: 'Quantity must be greater than {{ compared_value }}',
                    ),
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ComplaintItemData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
