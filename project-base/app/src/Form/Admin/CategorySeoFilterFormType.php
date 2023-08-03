<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Category\Category;
use App\Model\CategorySeo\CategorySeoFacade;
use App\Model\CategorySeo\CategorySeoFiltersData;
use Shopsys\FormTypesBundle\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorySeoFilterFormType extends AbstractType
{
    /**
     * @param \App\Model\CategorySeo\CategorySeoFacade $categorySeoFacade
     */
    public function __construct(
        private CategorySeoFacade $categorySeoFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \App\Model\Category\Category $category */
        $category = $options['category'];

        $domainId = $options['domainId'];

        $builder
            ->add('useFlags', YesNoType::class, [
                'required' => false,
                'label' => t('By flag'),
                'data' => false,
            ])
            ->add('parameters', ChoiceType::class, [
                'label' => t('Products parameters of selected category'),
                'choices' => $this->categorySeoFacade->getParametersUsedByProductsInCategoryWithoutSlider($category, $domainId),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Show combinations'),
                'attr' => [
                    'class' => 'margin-top-20',
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
