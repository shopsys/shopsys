<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\BestsellingProduct;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\Constraints;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ProductType;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\BestsellingProductFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BestsellingProductFormType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('products', GroupType::class, [
                'label' => 'Bestselling products',
                'error_bubbling' => true,
                'constraints' => [
                    new Constraints\UniqueCollection(
                        allowEmpty: true,
                        message: 'You entered same product twice. In list of bestsellers can be product only once. '
                            . 'Please correct it and then save form again.',
                    ),
                ],
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_bestsellingproduct_list',
                'save_label' => t('Save'),
            ]);

        for ($i = 0; $i < BestsellingProductFacade::MAX_RESULTS_ADMIN; $i++) {
            $builder->get('products')
                ->add((string)$i, ProductType::class, [
                    'required' => false,
                    'label' => $i + 1,
                    'label_attr' => [
                        'class' => 'col-auto',
                    ],
                    'row_attr' => [
                        'class' => 'input-group',
                    ],
                    'placeholder' => '-- auto filled --',
                    'enable_remove' => true,
                ]);
        }
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
