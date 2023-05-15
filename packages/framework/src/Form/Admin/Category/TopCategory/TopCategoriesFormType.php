<?php

namespace Shopsys\FrameworkBundle\Form\Admin\Category\TopCategory;

use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TopCategoriesFormType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer
     * @param \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer
     */
    public function __construct(
        private readonly CategoryFacade $categoryFacade,
        private readonly RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer,
        private readonly CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categoryPaths = $this->categoryFacade->getFullPathsIndexedByIdsForDomain(
            $options['domain_id'],
            $options['locale'],
        );

        $builder
            ->add(
                $builder
                    ->create('categories', SortableValuesType::class, [
                        'labels_by_value' => $categoryPaths,
                        'required' => false,
                    ])
                    ->addViewTransformer($this->removeDuplicatesTransformer)
                    ->addModelTransformer($this->categoriesIdsToCategoriesTransformer),
            )
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['domain_id', 'locale'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('locale', 'string')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
