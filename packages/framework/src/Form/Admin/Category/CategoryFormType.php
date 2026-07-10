<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Category;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        private readonly SeoSettingFacade $seoSettingFacade,
        private readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        private readonly Localization $localization,
        private readonly ParameterRepository $parameterRepository,
        private readonly CategoryAutomatedFilterFacade $categoryAutomatedFilterFacade,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $seoTitlesOptionsByDomainId = [];
        $seoMetaDescriptionsOptionsByDomainId = [];
        $seoH1OptionsByDomainId = [];

        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();

            $seoTitlesOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->getCategoryNameForPlaceholder($domainConfig, $options['category']),
                    'data-js-placeholder-source-input-id' => 'category_form_settings_name_' . $domainConfig->getLocale(),
                    'data-js-recommended-length' => 60,
                ],
            ];
            $seoMetaDescriptionsOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->seoSettingFacade->getDescriptionMainPage($domainId),
                    'data-js-recommended-length' => 155,
                ],
            ];
            $seoH1OptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->getCategoryNameForPlaceholder($domainConfig, $options['category']),
                    'data-js-placeholder-source-input-id' => 'category_form_settings_name_' . $domainConfig->getLocale(),
                ],
            ];
        }

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

        $builderSeoGroup = $builder->create('seo', GroupType::class, [
            'label' => 'SEO',
        ]);

        $builderSeoGroup
            ->add('seoTitles', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'options_by_domain_id' => $seoTitlesOptionsByDomainId,
                'label' => 'Page title',
            ])
            ->add('seoMetaDescriptions', MultidomainType::class, [
                'entry_type' => TextareaType::class,
                'required' => false,
                'options_by_domain_id' => $seoMetaDescriptionsOptionsByDomainId,
                'label' => 'Meta description',
            ])
            ->add('seoH1s', MultidomainType::class, [
                'required' => false,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Heading (H1) cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
                'options_by_domain_id' => $seoH1OptionsByDomainId,
                'label' => 'Heading (H1)',
            ]);

        if ($options['scenario'] === self::SCENARIO_EDIT) {
            $builderSeoGroup
                ->add('urls', UrlListType::class, [
                    'route_name' => 'front_product_list',
                    'entity_id' => $options['category']?->getId(),
                    'label' => 'URL addresses',
                ]);
        }

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
                    new Constraints\Image(
                        mimeTypes: ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        mimeTypesMessage: 'Image can be only in JPG, GIF or PNG format',
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

    private function getCategoryNameForPlaceholder(DomainConfig $domainConfig, ?Category $category = null): string
    {
        $domainLocale = $domainConfig->getLocale();

        return $category === null ? '' : $category->getName($domainLocale) ?? '';
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
