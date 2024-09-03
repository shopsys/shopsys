<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Complaint;

use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ComplaintItemFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productName', TextType::class, [
                'disabled' => true,
            ])
            ->add('catnum', TextType::class, [
                'disabled' => true,
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter description']),
                ],
                'error_bubbling' => true,
            ])
            ->add('quantity', IntegerType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter quantity']),
                    new Constraints\GreaterThan(
                        ['value' => 0, 'message' => 'Quantity must be greater than {{ compared_value }}'],
                    ),
                ],
                'error_bubbling' => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ComplaintItemData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
