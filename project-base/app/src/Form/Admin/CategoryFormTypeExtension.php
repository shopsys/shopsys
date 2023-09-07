<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use App\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer $removeDuplicatesFromArrayTransformer
     * @param \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        private readonly ParameterRepository $parameterRepository,
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
        $this->buildFilterParameters($builder, $options['category']);

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
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Category\Category|null $category
     */
    private function buildFilterParameters(FormBuilderInterface $builder, ?Category $category): void
    {
        if ($category === null) {
            return;
        }
        $parametersFilterBuilder = $builder->add('parametersGroup', GroupType::class, ['label' => t('Filter parameters')]);

        $parameterNamesById = [];

        foreach ($this->parameterRepository->getParametersUsedByProductsInCategory($category, Domain::FIRST_DOMAIN_ID) as $parameter) {
            $parameterNamesById[$parameter->getId()] = $parameter->getName();
        }

        $parametersFilterBuilder->add('parametersPosition', SortableValuesType::class, [
            'labels_by_value' => $parameterNamesById,
            'label' => t('Parameters order in category'),
            'required' => false,
        ]);

        $parametersFilterBuilder->add('parametersCollapsed', ChoiceType::class, [
            'required' => false,
            'label' => t('Filter parameters closed by default'),
            'choices' => $this->parameterRepository->getParametersUsedByProductsInCategory($category, Domain::FIRST_DOMAIN_ID),
            'expanded' => true,
            'choice_label' => 'name',
            'choice_value' => 'id',
            'multiple' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CategoryFormType::class;
    }
}
