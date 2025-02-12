<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\TopProduct;

use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TopProductsFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer
     */
    public function __construct(private readonly RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder
                    ->create('products', ProductsType::class, [
                        'label' => t('Products'),
                        'required' => false,
                        'sortable' => true,
                    ])
                    ->addViewTransformer($this->removeDuplicatesTransformer),
            )
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}
