<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Series;

use App\Model\Product\Series\Category\ProductSeriesCategoryFacade;
use App\Model\Product\Series\ProductSeries;
use App\Model\Product\Series\ProductSeriesData;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ProductSeriesFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    private $seoSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryFacade
     */
    private $productSeriesCategoryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryFacade $productSeriesCategoryFacade
     */
    public function __construct(
        SeoSettingFacade $seoSettingFacade,
        Domain $domain,
        ProductSeriesCategoryFacade $productSeriesCategoryFacade
    ) {
        $this->seoSettingFacade = $seoSettingFacade;
        $this->domain = $domain;
        $this->productSeriesCategoryFacade = $productSeriesCategoryFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \App\Model\Product\Series\ProductSeries|null $productSeries */
        $productSeries = $options['productSeries'];
        $builder->add($this->createDescriptionsGroup($builder));
        $builder->add($this->createImagesGroup($builder, $options));
        $builder->add($this->createSeoGroup($builder, $productSeries));
        $builder->add($this->createVisibilityGroup($builder));
        $builder->add($this->createCategoriesGroup($builder));
        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('productSeries')
            ->setAllowedTypes('productSeries', [ProductSeries::class, 'null'])
            ->setDefaults([
                'data_class' => ProductSeriesData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createCategoriesGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderCategoriesGroup = $builder->create('categoriesGroup', GroupType::class, [
            'label' => t('Kategorie produktových programů'),
        ]);

        $builderCategoriesGroup->add('productSeriesCategories', ChoiceType::class, [
            'required' => false,
            'choices' => $this->productSeriesCategoryFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
            'multiple' => true,
            'expanded' => true,
            'label' => t('Kategorie'),
        ]);

        return $builderCategoriesGroup;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createDescriptionsGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderDescriptionGroup = $builder->create('descriptionsGroup', GroupType::class, [
            'label' => t('Description'),
        ]);

        $builderDescriptionGroup->add('name', LocalizedType::class, [
            'required' => true,
            'entry_type' => TextType::class,
            'entry_options' => [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Jméno nábytkového programu musí být vyplněno']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Jméno nábytkového programu nemůže být delší než {{ limit }} znaků']),
                ],
            ],
            'label' => t('Název'),
        ])
        ->add('description', LocalizedType::class, [
            'label' => t('Popis'),
            'entry_type' => CKEditorType::class,
            'required' => true,
            'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
            'entry_options' => [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Popis nábytkového programu musí být vyplněn']),
                ],
            ],
        ]);

        return $builderDescriptionGroup;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createVisibilityGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderVisibilityGroup = $builder->create('visibilityGroup', GroupType::class, [
            'label' => t('Viditelnost'),
        ]);

        $builderVisibilityGroup
            ->add('hidden', MultidomainType::class, [
                'entry_type' => YesNoType::class,
                'required' => false,
                'label' => t('Skrýt'),
            ]);
        return $builderVisibilityGroup;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Series\ProductSeries|null $productSeries
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createSeoGroup(FormBuilderInterface $builder, ?ProductSeries $productSeries): FormBuilderInterface
    {
        $seoTitlesOptionsByDomainId = [];
        $seoMetaDescriptionsOptionsByDomainId = [];
        $seoH1OptionsByDomainId = [];
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();
            $seoTitlesOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->getTitlePlaceholder($locale, $productSeries),
                    'class' => 'js-dynamic-placeholder',
                    'data-placeholder-source-input-id' => 'product_form_name_' . $locale,
                ],
            ];
            $seoMetaDescriptionsOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->seoSettingFacade->getDescriptionMainPage($domainId),
                ],
            ];
            $seoH1OptionsByDomainId[$domainId] = $seoTitlesOptionsByDomainId[$domainId];
        }
        $builderSeoGroup = $builder->create('seoGroup', GroupType::class, [
            'label' => t('Seo'),
        ]);
        $builderSeoGroup
            ->add('seoTitle', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'options_by_domain_id' => $seoTitlesOptionsByDomainId,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => 60,
                ],
                'label' => t('Page title'),
            ])
            ->add('seoMetaDescription', MultidomainType::class, [
                'entry_type' => TextareaType::class,
                'required' => false,
                'options_by_domain_id' => $seoMetaDescriptionsOptionsByDomainId,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => 155,
                ],
                'label' => t('Meta description'),
            ])
            ->add('seoH1', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'options_by_domain_id' => $seoH1OptionsByDomainId,
                'label' => t('Heading (H1)'),
            ]);
        if ($productSeries) {
            $builderSeoGroup->add('url', UrlListType::class, [
                'route_name' => 'front_productseries_detail',
                'entity_id' => $productSeries->getId(),
                'label' => t('URL settings'),
            ]);
        }
        return $builderSeoGroup;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createImagesGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $builderImageGroup = $builder->create('imageGroup', GroupType::class, [
            'label' => t('Images'),
        ]);
        $builderImageGroup
            ->add('images', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => ProductSeries::class,
                'file_constraints' => [
                    new Constraints\Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'entity' => $options['productSeries'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
                'label' => t('Images'),
            ]);

        return $builderImageGroup;
    }

    /**
     * @param string $locale
     * @param \App\Model\Product\Series\ProductSeries|null $productSeries
     * @return string|null
     */
    private function getTitlePlaceholder(string $locale, ?ProductSeries $productSeries = null): ?string
    {
        return $productSeries !== null ? $productSeries->getName($locale) : '';
    }
}
