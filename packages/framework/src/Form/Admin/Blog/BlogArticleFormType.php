<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Blog;

use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\BlogCategoriesType;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\FormTypeLayout;
use Shopsys\FrameworkBundle\Form\GrapesJsType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleData;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\AbstractType;
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
        protected readonly ClockInterface $clock,
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

        foreach ($this->domain->getAllIds() as $domainId) {
            $categoriesOptionsByDomainId[$domainId] = [
                'domain_id' => $domainId,
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
            ->add('blogCategoriesByDomainId', MultidomainType::class, [
                'required' => false,
                'entry_type' => BlogCategoriesType::class,
                'options_by_domain_id' => $categoriesOptionsByDomainId,
                'label' => 'Assign to category',
            ])
            ->add('hidden', YesNoType::class, [
                'label' => 'Hide',
            ])
            ->add('visibleOnHomepage', YesNoType::class, [
                'label' => 'Visible on homepage',
            ])
            ->add('publishDate', DatePickerType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter date of creation'),
                ],
                'label' => 'Date of publication',
                'data' => $blogArticle === null ? $this->clock->now() : $blogArticle->getPublishDate(),
            ]);

        return $builderSettingsGroup;
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
                    new Constraints\Image(
                        mimeTypes: ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        mimeTypesMessage: 'Image can be only in JPG, GIF or PNG format',
                        maxSize: '15M',
                        maxSizeMessage: 'Uploaded image is to large ({{ size }} {{ suffix }}). '
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
