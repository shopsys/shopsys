<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Category\Category;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Svg\SvgProvider;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
     * @param \App\Model\Svg\SvgProvider $svgProvider
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     */
    public function __construct(
        SvgProvider $svgProvider,
        ParameterRepository $parameterRepository
    ) {
        $this->svgProvider = $svgProvider;
        $this->parameterRepository = $parameterRepository;
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
                'choices_as_values' => true,
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
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CategoryFormType::class;
    }
}
