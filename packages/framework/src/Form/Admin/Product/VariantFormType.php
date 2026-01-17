<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\ProductType;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class VariantFormType extends AbstractType
{
    public const string MAIN_VARIANT = 'mainVariant';
    public const string VARIANTS = 'variants';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::MAIN_VARIANT, ProductType::class, [
                'label' => 'Main variant',
                'allow_main_variants' => false,
                'allow_variants' => false,
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add(
                $builder
                    ->create(self::VARIANTS, ProductsType::class, [
                        'label' => 'Variants',
                        'allow_main_variants' => false,
                        'allow_variants' => false,
                        'constraints' => [
                            new Constraints\NotBlank(),
                        ],
                    ])
                    ->addModelTransformer(new RemoveDuplicatesFromArrayTransformer()),
            )
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_product_list',
                'save_label' => t('Create'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
