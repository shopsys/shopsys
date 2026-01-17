<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Blog;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DomainsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class BlogCategoryFormType extends AbstractType
{
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly Localization $localization,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builderSettingsGroup = $this->createSettingsGroup($builder, $options);
        $builderSeoGroup = $this->createSeoGroup($builder, $options);
        $builderDescriptionGroup = $this->createDescriptionGroup($builder);
        $builderImageGroup = $this->createImageGroup($builder, $options);

        $builder
            ->add($builderSettingsGroup)
            ->add($builderSeoGroup)
            ->add($builderDescriptionGroup)
            ->add($builderImageGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_blogcategory_list',
                'entity' => $options['blogCategory'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['blogCategory'])
            ->setAllowedTypes('blogCategory', [BlogCategory::class, 'null'])
            ->setDefaults([
                'data_class' => BlogCategoryData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    private function getCategoryNameForPlaceholder(
        DomainConfig $domainConfig,
        ?BlogCategory $blogCategory = null,
    ): ?string {
        $domainLocale = $domainConfig->getLocale();

        return $blogCategory === null ? '' : $blogCategory->getName($domainLocale);
    }

    private function createSettingsGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        if ($options['blogCategory'] !== null) {
            $parentChoices = $this->blogCategoryFacade->getTranslatedAllWithoutBranch($options['blogCategory'], $this->localization->getCurrentLocaleForTranslatableEntities());
        } else {
            $parentChoices = $this->blogCategoryFacade->getTranslatedAll($this->localization->getCurrentLocaleForTranslatableEntities());
        }

        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        if ($options['blogCategory'] !== null) {
            $builderSettingsGroup
                ->add('id', DisplayOnlyType::class, [
                    'data' => $options['blogCategory']->getId(),
                    'label' => 'ID',
                ]);
        }

        $builderSettingsGroup
            ->add('names', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'required' => false,
                    'constraints' => [
                        new Constraints\Length(['max' => 255, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters']),
                    ],
                ],
                'label' => 'Name',
            ])
            ->add('parent', ChoiceType::class, [
                'required' => true,
                'choices' => $parentChoices,
                'choice_label' => function (BlogCategory $blogCategory) {
                    $padding = str_repeat("\u{00a0}", $blogCategory->getLevel() * 2);

                    return $padding . $blogCategory->getName();
                },
                'choice_value' => 'id',
                'label' => 'Ancestor category',
            ])
            ->add('enabled', DomainsType::class, [
                'required' => false,
                'label' => 'Display on',
            ]);

        return $builderSettingsGroup;
    }

    private function prepareSeoData(array $options): array
    {
        $seoTitlesOptionsByDomainId = [];
        $seoMetaDescriptionsOptionsByDomainId = [];
        $seoH1OptionsByDomainId = [];

        foreach ($this->domain->getAdminEnabledDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();

            $seoTitlesOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->getCategoryNameForPlaceholder($domainConfig, $options['blogCategory']),
                    'data-js-placeholder-source-input-id' => 'blog_category_form_settings_names_' . $domainConfig->getLocale(),
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
                    'placeholder' => $this->getCategoryNameForPlaceholder($domainConfig, $options['blogCategory']),
                    'data-js-placeholder-source-input-id' => 'blog_category_form_settings_names_' . $domainConfig->getLocale(),
                ],
            ];
        }

        return [$seoTitlesOptionsByDomainId, $seoMetaDescriptionsOptionsByDomainId, $seoH1OptionsByDomainId];
    }

    private function createSeoGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        [$seoTitlesOptionsByDomainId, $seoMetaDescriptionsOptionsByDomainId, $seoH1OptionsByDomainId] = $this->prepareSeoData($options);

        $builderSeoGroup = $builder->create('seo', GroupType::class, [
            'label' => 'Seo',
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
                        new Constraints\Length(['max' => 255, 'maxMessage' => 'Heading (H1) cannot be longer than {{ limit }} characters']),
                    ],
                ],
                'options_by_domain_id' => $seoH1OptionsByDomainId,
                'label' => 'Heading (H1)',
            ]);

        if ($options['blogCategory'] !== null) {
            $builderSeoGroup
                ->add('urls', UrlListType::class, [
                    'route_name' => 'front_blogcategory_detail',
                    'entity_id' => $this->getBlogCategoryId($options['blogCategory']),
                    'label' => 'URL addresses',
                ]);
        }

        return $builderSeoGroup;
    }

    private function getBlogCategoryId(?BlogCategory $blogCategory): ?int
    {
        if ($blogCategory !== null) {
            return $blogCategory->getId();
        }

        return null;
    }

    private function createDescriptionGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderDescriptionGroup = $builder->create('description', GroupType::class, [
            'label' => 'Description',
        ]);

        $builderDescriptionGroup
            ->add('descriptions', LocalizedType::class, [
                'entry_type' => CKEditorType::class,
                'label' => 'Description',
                'required' => false,
            ]);

        return $builderDescriptionGroup;
    }

    private function createImageGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $builderImageGroup = $builder->create('image', GroupType::class, [
            'label' => 'Image',
        ]);

        $builderImageGroup
            ->add('image', ImageUploadType::class, [
                'required' => false,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '15M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'label' => 'Upload image',
                'entity' => $options['blogCategory'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        return $builderImageGroup;
    }
}
