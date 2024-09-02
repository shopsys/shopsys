<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer $removeDuplicatesFromArrayTransformer
     * @param \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        private readonly FormBuilderHelper $formBuilderHelper,
        private readonly CategoryFacade $categoryFacade,
        private readonly RemoveDuplicatesFromArrayTransformer $removeDuplicatesFromArrayTransformer,
        private readonly CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
        private readonly Localization $localization,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);

        $categoryPaths = $this->categoryFacade->getFullPathsIndexedByIds(
            $this->localization->getAdminLocale(),
        );
        $builder->get('seo')->add(
            $builder
                    ->create('linkedCategories', SortableValuesType::class, [
                        'labels_by_value' => $categoryPaths,
                        'required' => false,
                        'label' => t('Linked categories'),
                    ])
                    ->addViewTransformer($this->removeDuplicatesFromArrayTransformer)
                    ->addModelTransformer($this->categoriesIdsToCategoriesTransformer),
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CategoryFormType::class;
    }
}
