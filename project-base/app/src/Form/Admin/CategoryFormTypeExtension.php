<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Svg\SvgProvider;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
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
     * @var \App\Model\Svg\SvgProvider
     */
    private $svgProvider;

    /**
     * @var \App\Model\Product\Parameter\ParameterRepository
     */
    private $parameterRepository;

    /**
     * @var \App\Component\Form\FormBuilderHelper
     */
    private $formBuilderHelper;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private CategoryFacade $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer
     */
    private RemoveDuplicatesFromArrayTransformer $removeDuplicatesFromArrayTransformer;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer
     */
    private CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private Localization $localization;

    /**
     * @param \App\Model\Svg\SvgProvider $svgProvider
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer $removeDuplicatesFromArrayTransformer
     * @param \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        SvgProvider $svgProvider,
        ParameterRepository $parameterRepository,
        FormBuilderHelper $formBuilderHelper,
        CategoryFacade $categoryFacade,
        RemoveDuplicatesFromArrayTransformer $removeDuplicatesFromArrayTransformer,
        CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
        Localization $localization
    ) {
        $this->svgProvider = $svgProvider;
        $this->parameterRepository = $parameterRepository;
        $this->formBuilderHelper = $formBuilderHelper;
        $this->categoryFacade = $categoryFacade;
        $this->removeDuplicatesFromArrayTransformer = $removeDuplicatesFromArrayTransformer;
        $this->categoriesIdsToCategoriesTransformer = $categoriesIdsToCategoriesTransformer;
        $this->localization = $localization;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $settingsBuilder = $builder->get('settings');
        $settingsBuilder
            ->add('svgIcon', ChoiceType::class, [
                'label' => t('Nastavení SVG ikony'),
                'required' => false,
                'choices' => $this->svgProvider->getAllSvgIconsNames(),
            ]);

        /** @var \Ivory\OrderedForm\Builder\OrderedFormBuilder $builderShortDescriptionGroup */
        $builderShortDescriptionGroup = $builder->create('shortDescriptionGroup', GroupType::class, [
            'label' => t('Krátký popis'),
        ]);

        $builderShortDescriptionGroup->add('shortDescription', MultidomainType::class, [
            'entry_type' => CKEditorType::class,
            'required' => false,
            'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
        ]);

        $builder->add($builderShortDescriptionGroup);

        $builderShortDescriptionGroup->setPosition(['after' => 'seo']);

        $this->buildFilterParameters($builder, $options['category'], $options);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);

        $categoryPaths = $this->categoryFacade->getFullPathsIndexedByIds(
            $this->localization->getAdminLocale()
        );
        $builder->get('seo')->add(
            $builder
                    ->create('linkedCategories', SortableValuesType::class, [
                        'labels_by_value' => $categoryPaths,
                        'required' => false,
                        'label' => t('Propojené kategorie'),
                    ])
                    ->addViewTransformer($this->removeDuplicatesFromArrayTransformer)
                    ->addModelTransformer($this->categoriesIdsToCategoriesTransformer)
        );
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Category\Category|null $category
     * @param array $options
     */
    private function buildFilterParameters(FormBuilderInterface $builder, ?Category $category, array $options): void
    {
        if ($category === null) {
            return;
        }
        $parametersFilterBuilder = $builder->add('parametersGroup', GroupType::class, ['label' => t('Parametry filtru')]);

        $parameterNamesById = [];
        foreach ($this->parameterRepository->getParametersUsedByProductsInCategory($category, Domain::FIRST_DOMAIN_ID) as $parameter) {
            $parameterNamesById[$parameter->getId()] = $parameter->getName();
        }

        $parametersFilterBuilder->add('parametersPosition', SortableValuesType::class, [
            'labels_by_value' => $parameterNamesById,
            'label' => t('Parametry a pořadí v kategorii'),
            'required' => false,
        ]);

        $parametersFilterBuilder->add('parametersCollapsed', ChoiceType::class, [
            'required' => false,
            'label' => t('Defaultně zavřené parametry ve filtru'),
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
