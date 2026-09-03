<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Category;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Form\Admin\Seo\SeoGroupType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class CategoryFormType extends AbstractType
{
    public const string SCENARIO_CREATE = 'create';
    public const string SCENARIO_EDIT = 'edit';

    public function __construct(
        private readonly CategoryFacade $categoryFacade,
        private readonly Domain $domain,
        private readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        private readonly Localization $localization,
        private readonly ParameterRepository $parameterRepository,
        private readonly CategoryAutomatedFilterFacade $categoryAutomatedFilterFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['category'] !== null) {
            $parentChoices = $this->categoryFacade->getAllTranslatedWithoutBranch(
                $options['category'],
                $this->localization->getCurrentLocaleForTranslatableEntities(),
            );
        } else {
            $parentChoices = $this->categoryFacade->getAllTranslated($this->localization->getCurrentLocaleForTranslatableEntities());
        }

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['category']->getId(),
                    'label' => 'ID',
                ]);
        }

        $categoryAutomatedFiltersNotesIndexedByValue = $this->categoryAutomatedFilterFacade->getNotesIndexedByValue();

        $builderSettingsGroup
            ->add('name', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'required' => false,
                    'constraints' => [
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Name cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
                'label' => 'Name',
            ])
            ->add('parent', ChoiceType::class, [
                'required' => false,
                'choices' => $parentChoices,
                'choice_label' => function (Category $category) {
                    $padding = str_repeat("\u{00a0}", ($category->getLevel() - 1) * 2);

                    return $padding . $category->getName();
                },
                'choice_value' => 'id',
                'label' => 'Parent category',
            ])
            ->add('enabled', DomainsType::class, [
                'required' => false,
                'label' => 'Display on',
            ])
            ->add('automatedFilters', ChoiceType::class, [
                'label' => 'Automated filters',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $this->categoryAutomatedFilterFacade->getAllValuesIndexedByLabel(),
                'choice_attr' => function ($choice, $key, $value) use ($categoryAutomatedFiltersNotesIndexedByValue) {
                    $help = $categoryAutomatedFiltersNotesIndexedByValue[$value] ?? null;

                    if ($help === null) {
                        return [];
                    }

                    return [
                        'data-help' => $help,
                    ];
                },
            ]);

        $builderSeoGroup = $builder->create('seoGroup', SeoGroupType::class, [
            'placeholder_source_input_id' => 'category_form_settings_name_{locale}',
            'url_list_options' => $options['scenario'] === self::SCENARIO_EDIT ? [
                'route_name' => 'front_product_list',
                'entity_id' => $options['category']?->getId(),
            ] : null,
        ]);

        $builderDescriptionGroup = $builder->create('description', GroupType::class, [
            'label' => 'Description',
        ]);

        $builderDescriptionGroup
            ->add('descriptions', MultidomainType::class, [
                'entry_type' => CKEditorType::class,
                'required' => false,
            ]);

        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Category::class,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'label' => 'Upload image',
                'entity' => $options['category'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        $builder
            ->add($builderSettingsGroup)
            ->add($builderSeoGroup)
            ->add($builderDescriptionGroup)
            ->add($builderImageGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_category_list',
                'entity' => $options['category'],
            ]);

        $this->pluginCrudExtensionFacade->extendForm($builder, 'category', 'pluginData');

        $this->buildFilterParameters($builder, $options['category']);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['scenario', 'category'])
            ->setAllowedTypes('category', [Category::class, 'null'])
            ->setAllowedValues('scenario', [self::SCENARIO_CREATE, self::SCENARIO_EDIT])
            ->setDefaults([
                'data_class' => CategoryData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    protected function buildFilterParameters(
        FormBuilderInterface $builder,
        ?Category $category,
    ): void {
        if ($category === null) {
            return;
        }
        $parametersFilterBuilder = $builder->create('parametersGroup', GroupType::class, ['label' => t('Filter parameters')]);

        $parameterNamesById = [];

        $parametersUsedByProductsInCategory = $this->parameterRepository->getParametersUsedByProductsInCategory($category, $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID));

        foreach ($parametersUsedByProductsInCategory as $parameter) {
            $parameterNamesById[$parameter->getId()] = $parameter->getName();
        }

        $parametersFilterBuilder->add('parametersPosition', SortableValuesType::class, [
            'entry_type' => IntegerType::class,
            'labels_by_value' => $parameterNamesById,
            'label' => 'Parameters order in category',
            'required' => false,
        ]);

        $parametersFilterBuilder->add('parametersCollapsed', ChoiceType::class, [
            'required' => false,
            'label' => 'Filter parameters closed by default',
            'choices' => $parametersUsedByProductsInCategory,
            'expanded' => true,
            'choice_label' => 'name',
            'choice_value' => 'id',
            'multiple' => true,
        ]);

        $builder->add($parametersFilterBuilder);
    }
}
