<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Transformers\ProductSeriesIdsToProductSeriesTransformer;
use App\Model\Category\Category;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Series\ProductSeriesFacade;
use App\Model\Svg\SvgProvider;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \App\Model\Svg\SvgProvider
     */
    private $svgProvider;

    /**
     * @var \App\Model\Product\Parameter\ParameterRepository
     */
    private $parameterRepository;

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacade
     */
    private $productSeriesFacade;

    /**
     * @var \App\Form\Transformers\ProductSeriesIdsToProductSeriesTransformer
     */
    private $productSeriesIdsToProductSeriesTransformer;

    /**
     * @param \App\Model\Svg\SvgProvider $svgProvider
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Model\Product\Series\ProductSeriesFacade $productSeriesFacade
     * @param \App\Form\Transformers\ProductSeriesIdsToProductSeriesTransformer $productSeriesIdsToProductSeriesTransformer
     */
    public function __construct(
        SvgProvider $svgProvider,
        ParameterRepository $parameterRepository,
        ProductSeriesFacade $productSeriesFacade,
        ProductSeriesIdsToProductSeriesTransformer $productSeriesIdsToProductSeriesTransformer
    ) {
        $this->svgProvider = $svgProvider;
        $this->parameterRepository = $parameterRepository;
        $this->productSeriesFacade = $productSeriesFacade;
        $this->productSeriesIdsToProductSeriesTransformer = $productSeriesIdsToProductSeriesTransformer;
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

        $this->buildFilterParameters($builder, $options['category']);

        $this->buildCategoryProductSeriesBlock($builder);
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
        $parametersFilterBuilder = $builder->add('parametersGroup', GroupType::class, ['label' => t('Parametry filtru')]);
        $parametersFilterBuilder->add('parameters', ChoiceType::class, [
            'required' => false,
            'label' => t('Parametry:'),
            'choices' => $this->parameterRepository->getParametersUsedByProductsInCategory($category, Domain::FIRST_DOMAIN_ID),
            'expanded' => true,
            'choice_label' => 'name',
            'choice_value' => 'id',
            'multiple' => true,
        ]);

        $parametersFilterBuilder->add('parametersCollapsed', ChoiceType::class, [
            'required' => false,
            'label' => t('Defaultně zavřené parametry ve filtru:'),
            'choices' => $this->parameterRepository->getParametersUsedByProductsInCategory($category, Domain::FIRST_DOMAIN_ID),
            'expanded' => true,
            'choice_label' => 'name',
            'choice_value' => 'id',
            'multiple' => true,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function buildCategoryProductSeriesBlock(FormBuilderInterface $builder): void
    {
        $allProductSeriesNamesById = $this->productSeriesFacade->getAllProductSeriesNamesIndexedById();

        $builderCategoryProductSeriesBlock = $builder->create('categoryProductSeriesSelector', GroupType::class, [
            'label' => t('Programy produktů kategorie'),
        ]);

        $builderCategoryProductSeriesBlock->add('productSeriesListTitle', MultidomainType::class, [
            'entry_type' => TextareaType::class,
            'required' => false,
            'macro' => [
                'name' => 'seoFormRowMacros.multidomainRow',
                'recommended_length' => null,
            ],
            'label' => t('Titulek výpisu produktových programů'),
        ]);

        $builderCategoryProductSeriesBlock->add('productSeriesListDescription', MultidomainType::class, [
            'entry_type' => TextareaType::class,
            'required' => false,
            'macro' => [
                'name' => 'seoFormRowMacros.multidomainRow',
                'recommended_length' => null,
            ],
            'label' => t('Popisek výpisu produktových programů'),
        ]);

        $builderCategoryProductSeriesBlock->add('productSeriesListLink', MultidomainType::class, [
            'entry_type' => TextareaType::class,
            'required' => false,
            'macro' => [
                'name' => 'seoFormRowMacros.multidomainRow',
                'recommended_length' => null,
            ],
            'label' => t('Odkaz na výpis produktových programů'),
        ]);

        $builderCategoryProductSeriesBlock->add($builder
            ->create('categoryProductSeries', SortableValuesType::class, [
                'labels_by_value' => $allProductSeriesNamesById,
                'label' => t('Vyberte Program'),
                'required' => false,
            ])
            ->addModelTransformer($this->productSeriesIdsToProductSeriesTransformer));

        $builder->add($builderCategoryProductSeriesBlock);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield CategoryFormType::class;
    }
}
