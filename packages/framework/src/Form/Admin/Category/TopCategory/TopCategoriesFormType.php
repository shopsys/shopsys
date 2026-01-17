<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Category\TopCategory;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TopCategoriesFormType extends AbstractType
{
    public function __construct(
        private readonly CategoryFacade $categoryFacade,
        private readonly RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer,
        private readonly CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoryPaths = $this->categoryFacade->getFullPathsIndexedByIdsForDomain(
            $options['domain_id'],
            $options['locale'],
        );

        $builder
            ->add(
                $builder
                    ->create('categories', SortableValuesType::class, [
                        'label' => 'Category',
                        'labels_by_value' => $categoryPaths,
                        'required' => false,
                    ])
                    ->addViewTransformer($this->removeDuplicatesTransformer)
                    ->addModelTransformer($this->categoriesIdsToCategoriesTransformer),
            )
            ->add('actionBar', ActionBarType::class, [
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
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
