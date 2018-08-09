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
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer
     */
    private $removeDuplicatesTransformer;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer
     */
    private $categoriesIdsToCategoriesTransformer;

    public function __construct(
        CategoryFacade $categoryFacade,
        RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer,
        CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->removeDuplicatesTransformer = $removeDuplicatesTransformer;
        $this->categoriesIdsToCategoriesTransformer = $categoriesIdsToCategoriesTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categoryPaths = $this->categoryFacade->getFullPathsIndexedByIdsForDomain(
            $options['domain_id'],
            $options['locale']
        );

        $builder
            ->add(
                $builder
                    ->create('categories', SortableValuesType::class, [
                        'labels_by_value' => $categoryPaths,
                        'required' => false,
                    ])
                    ->addViewTransformer($this->removeDuplicatesTransformer)
                    ->addModelTransformer($this->categoriesIdsToCategoriesTransformer)
            )
            ->add('save', SubmitType::class);
    }

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
