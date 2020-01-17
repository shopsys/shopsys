<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Series;

use App\Model\Product\Series\ProductSeries;
use App\Model\Product\Series\ProductSeriesData;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ProductSeriesFormType extends AbstractType
{
    public const CSRF_TOKEN_ID = 'productseries_edit_type';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    private $seoSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        SeoSettingFacade $seoSettingFacade,
        Domain $domain
    ) {
        $this->seoSettingFacade = $seoSettingFacade;
        $this->domain = $domain;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $productSeries = $options['productSeries'];
        /* @var $productSeries \App\Model\Product\Series\ProductSeries|null */

        $builder->add($this->createDescriptionsGroup($builder, $productSeries));
        $builder->add($this->createVisibilityGroup($builder));
        $builder->add($this->createSeoGroup($builder, $productSeries));
        $builder->add($this->createImagesGroup($builder, $options));
        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('productSeries')
            ->setAllowedTypes('productSeries', [ProductSeries::class, 'null'])
            ->setDefaults([
                'data_class' => ProductSeriesData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'csrf_token_id' => self::CSRF_TOKEN_ID,
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Series\ProductSeries|null $productSeries
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createDescriptionsGroup(FormBuilderInterface $builder, ?ProductSeries $productSeries)
    {
        $builderDescriptionGroup = $builder->create('descriptionsGroup', GroupType::class, [
            'label' => t('Description'),
        ]);

        $builderDescriptionGroup->add('names', LocalizedType::class, [
            'required' => false,
            'entry_type' => TextType::class,
            'entry_options' => [
                'constraints' => [
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Product name cannot be longer than {{ limit }} characters']),
                ],
            ],
            'label' => t('Název'),
        ])
        ->add('descriptions', LocalizedType::class, [
            'label' => t('Popis'),
            'entry_type' => CKEditorType::class,
            'required' => false,
            'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
        ]);

        return $builderDescriptionGroup;
    }

    private function createVisibilityGroup(FormBuilderInterface $builder){
        $builderVisibilityGroup = $builder->create('visibilityGroup', GroupType::class, [
            'label' => t('Viditelnost'),
        ]);

        $domainLabelOptions = [];
        foreach ($this->domain->getAll() as $domain){
            $domainLabelOptions[$domain->getId()]['label'] = t('Skrýt pro '  . $domain->getName());
        }

        $builderVisibilityGroup
            ->add('hidden', MultidomainType::class, [
                'entry_type' => CheckboxType::class,
                'required' => false,
                'entry_options' => ['data' => true],
                'label' => t('Skrýt'),
                'options_by_domain_id' => $domainLabelOptions,
            ]);
        return $builderVisibilityGroup;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Series\ProductSeries|null $productSeries
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createSeoGroup(FormBuilderInterface $builder, ?ProductSeries $productSeries)
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
            ->add('seoTitles', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'options_by_domain_id' => $seoTitlesOptionsByDomainId,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => 60,
                ],
                'label' => t('Page title'),
            ])
            ->add('seoMetaDescriptions', MultidomainType::class, [
                'entry_type' => TextareaType::class,
                'required' => false,
                'options_by_domain_id' => $seoMetaDescriptionsOptionsByDomainId,
                'macro' => [
                    'name' => 'seoFormRowMacros.multidomainRow',
                    'recommended_length' => 155,
                ],
                'label' => t('Meta description'),
            ])
            ->add('seoH1s', MultidomainType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'options_by_domain_id' => $seoH1OptionsByDomainId,
                'label' => t('Heading (H1)'),
            ]);
        if ($productSeries) {
            $builderSeoGroup->add('urls', UrlListType::class, [
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
                'image_entity_class' => Product::class,
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
     * @param $locale
     * @param \App\Model\Product\Series\ProductSeries|null $productSeries
     * @return string|null
     */
    private function getTitlePlaceholder($locale, ?ProductSeries $productSeries = null)
    {
        return $productSeries !== null ? $productSeries->getName($locale) : '';
    }
}
