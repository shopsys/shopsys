<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\TopProduct;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TopProductsFormType extends AbstractType
{
    public function __construct(private readonly RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer)
    {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder
                    ->create('products', ProductsType::class, [
                        'label' => 'Products',
                        'required' => false,
                        'sortable' => true,
                    ])
                    ->addViewTransformer($this->removeDuplicatesTransformer),
            )
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
