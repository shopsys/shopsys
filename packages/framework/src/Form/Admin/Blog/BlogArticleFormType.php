<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Blog;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Form\BlogArticleAuthorPickerType;
use Shopsys\FrameworkBundle\Form\BlogCategoriesType;
use Shopsys\FrameworkBundle\Form\DateTimeType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\FormTypeLayout;
use Shopsys\FrameworkBundle\Form\GrapesJsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\InlineLabeledFieldType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleData;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleStatusEnum;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class BlogArticleFormType extends AbstractType
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly SeoSettingFacade $seoSettingFacade,
        protected readonly BlogArticleStatusEnum $blogArticleStatusEnum,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle|null $blogArticle */
        $blogArticle = $options['blogArticle'];

        $builderSettingsGroup = $this->createSettingsGroup($builder, $blogArticle);
        $builderSeoGroup = $this->createSeoGroup($builder, $blogArticle);
        $builderDescriptionGroup = $this->createDescriptionGroup($builder);
        $builderImageGroup = $this->createImageGroup($builder, $options);
        $builderPerexGroup = $this->createPerexGroup($builder);

        $builder
            ->add($builderSettingsGroup)
            ->add($builderSeoGroup)
            ->add($builderPerexGroup)
            ->add($builderDescriptionGroup)
            ->add($builderImageGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_blogarticle_list',
                'entity' => $options['blogArticle'],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['blogArticle'])
            ->setAllowedTypes('blogArticle', [BlogArticle::class, 'null'])
            ->setDefaults([
                'data_class' => BlogArticleData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    private function getArticleNameForPlaceholder(
        DomainConfig $domainConfig,
        ?BlogArticle $blogArticle = null,
    ): ?string {
        $domainLocale = $domainConfig->getLocale();

        return $blogArticle === null ? '' : $blogArticle->getName($domainLocale);
    }

    private function createSeoGroup(FormBuilderInterface $builder, ?BlogArticle $blogArticle): FormBuilderInterface
    {
        [$seoTitlesOptionsByDomainId, $seoMetaDescriptionsOptionsByDomainId, $seoH1OptionsByDomainId] = $this->prepareSeoData($blogArticle);

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
                        new Constraints\Length(max: 255, maxMessage: 'Heading (H1) cannot be longer than {{ limit }} characters'),
                    ],
                ],
                'options_by_domain_id' => $seoH1OptionsByDomainId,
                'label' => 'Heading (H1)',
            ]);

        if ($blogArticle !== null) {
            $builderSeoGroup
                ->add('urls', UrlListType::class, [
                    'route_name' => 'front_blogarticle_detail',
                    'entity_id' => $blogArticle->getId(),
                    'label' => 'URL addresses',
                ]);
        }

        return $builderSeoGroup;
    }

    private function createSettingsGroup(
        FormBuilderInterface $builder,
        ?BlogArticle $blogArticle,
    ): FormBuilderInterface {
        $builderSettingsGroup = $builder->create('settings', GroupType::class, [
            'label' => 'Settings',
        ]);

        $categoriesOptionsByDomainId = [];
        $statusesOptionsByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoriesOptionsByDomainId[$domainId] = [
                'domain_id' => $domainId,
            ];

            $previewUrl = $this->getArticlePreviewUrl($blogArticle, $domainId);
            $statusDescriptions = $this->blogArticleStatusEnum->getStatusDescriptions($previewUrl);

            $currentStatus = $blogArticle?->getStatus($domainId) ?? BlogArticleStatusEnum::STATUS_DRAFT;
            $statusesOptionsByDomainId[$domainId] = [
                'help' => $statusDescriptions[$currentStatus] ?? '',
                'help_html' => true,
                'attr' => [
                    'data-js-status-description' => json_encode($statusDescriptions),
                ],
            ];
        }

        $builderSettingsGroup
            ->add('names', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'required' => false,
                    'constraints' => [
                        new Constraints\Length(max: 255, maxMessage: 'Name cannot be longer than {{ limit }} characters'),
                    ],
                ],
                'label' => 'Name',
            ])
            ->add('blogArticleAuthor', BlogArticleAuthorPickerType::class, [
                'required' => false,
                'label' => 'Author',
            ])
            ->add('blogCategoriesByDomainId', MultidomainType::class, [
                'required' => false,
                'entry_type' => BlogCategoriesType::class,
                'options_by_domain_id' => $categoriesOptionsByDomainId,
                'label' => 'Assign to category',
            ])
            ->add('visibleOnHomepage', InlineLabeledFieldType::class, [
                'entry_type' => YesNoType::class,
                'label' => 'Visible on homepage',
            ])
            ->add('statuses', MultidomainType::class, [
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'choices' => $this->blogArticleStatusEnum->getAllIndexedByTranslations(),
                ],
                'options_by_domain_id' => $statusesOptionsByDomainId,
                'label' => 'Status',
            ]);

        if ($blogArticle !== null) {
            $builderSettingsGroup->add('createdAt', InlineLabeledFieldType::class, [
                'entry_type' => DisplayOnlyType::class,
                'entry_options' => [
                    'data' => $this->dateTimeFormatterExtension->formatDateTime($blogArticle->getCreatedAt()),
                ],
                'label' => 'Date of creation',
            ]);
        }

        $builderSettingsGroup
            ->add('publishDates', MultidomainType::class, [
                'entry_type' => DateTimeType::class,
                'required' => false,
                'label' => 'Date of publication',
            ]);

        return $builderSettingsGroup;
    }

    private function getArticlePreviewUrl(?BlogArticle $blogArticle, int $domainId): ?string
    {
        if ($blogArticle === null) {
            return null;
        }

        try {
            return $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId($domainId, 'front_blogarticle_detail', $blogArticle->getId());
        } catch (FriendlyUrlNotFoundException) {
            return null;
        }
    }

    private function createDescriptionGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderDescriptionGroup = $builder->create('description', GroupType::class, [
            'label' => 'Description',
        ]);

        $builderDescriptionGroup
            ->add('descriptions', LocalizedType::class, [
                'layout' => FormTypeLayout::LAYOUT_BLOCK,
                'entry_type' => GrapesJsType::class,
                'entry_options' => [
                    'allow_products' => true,
                ],
                'label' => 'Description',
                'required' => false,
            ]);

        return $builderDescriptionGroup;
    }

    private function createPerexGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderDescriptionGroup = $builder->create('perex', GroupType::class, [
            'label' => 'Perex',
        ]);

        $builderDescriptionGroup
            ->add('perexes', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'required' => false,
                ],
                'label' => 'Perex',
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
                'image_entity_class' => BlogArticle::class,
                'image_type' => null,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '15M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'label' => 'Upload image',
                'entity' => $options['blogArticle'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
            ]);

        return $builderImageGroup;
    }

    private function prepareSeoData(?BlogArticle $blogArticle): array
    {
        $seoTitlesOptionsByDomainId = [];
        $seoMetaDescriptionsOptionsByDomainId = [];
        $seoH1OptionsByDomainId = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();

            $seoTitlesOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->getArticleNameForPlaceholder($domainConfig, $blogArticle),
                    'data-js-placeholder-source-input-id' => 'blog_article_form_settings_names_' . $domainConfig->getLocale(),
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
                    'placeholder' => $this->getArticleNameForPlaceholder($domainConfig, $blogArticle),
                    'data-js-placeholder-source-input-id' => 'blog_article_form_settings_names_' . $domainConfig->getLocale(),
                ],
            ];
        }

        return [$seoTitlesOptionsByDomainId, $seoMetaDescriptionsOptionsByDomainId, $seoH1OptionsByDomainId];
    }
}
