<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Product;

use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\ProductType;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class VariantFormType extends AbstractType
{
    const MAIN_VARIANT = 'mainVariant';
    const VARIANTS = 'variants';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(self::MAIN_VARIANT, ProductType::class, [
                'allow_main_variants' => false,
                'allow_variants' => false,
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add(
                $builder
                    ->create(self::VARIANTS, ProductsType::class, [
                        'allow_main_variants' => false,
                        'allow_variants' => false,
                        'constraints' => [
                            new Constraints\NotBlank(),
                        ],
                    ])
                    ->addModelTransformer(new RemoveDuplicatesFromArrayTransformer())
            )
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
