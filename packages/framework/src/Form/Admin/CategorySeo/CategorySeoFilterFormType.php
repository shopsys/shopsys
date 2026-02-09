<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\CategorySeo;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFacade;
use Shopsys\FrameworkBundle\Model\CategorySeo\CategorySeoFiltersData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategorySeoFilterFormType extends AbstractType
{
    public function __construct(
        private readonly CategorySeoFacade $categorySeoFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Category\Category $category */
        $category = $options['category'];

        $domainId = $options['domainId'];

        $builder
            ->add('useFlags', YesNoType::class, [
                'label' => 'By flag',
                'data' => false,
            ])
            ->add('parameters', ChoiceType::class, [
                'label' => 'Products parameters of selected category',
                'choices' => $this->categorySeoFacade->getParametersUsedByProductsInCategoryWithoutSlider($category, $domainId),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_categoryseo_newcategory',
                'back_label' => t('Back to category selection'),
                'save_label' => t('Show combinations'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('category')
            ->setAllowedTypes('category', Category::class)
            ->setRequired('domainId')
            ->setAllowedTypes('domainId', 'int')
            ->setDefaults([
                'data_class' => CategorySeoFiltersData::class,
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
            ]);
    }
}
