<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Series;

use App\Model\Product\Series\Category\ProductSeriesCategory;
use App\Model\Product\Series\Category\ProductSeriesCategoryData;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class ProductSeriesCategoryFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    private $seoSettingFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @param \App\Component\Domain\Domain $domain
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \App\Model\Product\Series\Category\ProductSeriesCategory|null $productSeriesCategory */
        $productSeriesCategory = $options['productSeriesCategory'];
        $builder->add($this->createDescriptionsGroup($builder));
        $builder->add($this->createSeoGroup($builder, $productSeriesCategory));
        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('productSeriesCategory')
            ->setAllowedTypes('productSeriesCategory', [ProductSeriesCategory::class, 'null'])
            ->setDefaults([
                'data_class' => ProductSeriesCategoryData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createDescriptionsGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderDescriptionGroup = $builder->create('descriptionsGroup', GroupType::class, [
            'label' => t('Popis'),
        ]);

        $builderDescriptionGroup->add('name', LocalizedType::class, [
            'required' => true,
            'entry_type' => TextType::class,
            'entry_options' => [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Název kategorie musí být vyplněn']),
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Název kategorie produktového programu nemůže být delší než {{ limit }} znaků']),
                ],
            ],
            'label' => t('Název kategorie'),
        ])
        ->add('description', LocalizedType::class, [
            'label' => t('Popis kategorie'),
            'entry_type' => CKEditorType::class,
            'required' => true,
            'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
            'entry_options' => [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Popis kategorie produktového programu musí být vyplněn']),
                ],
            ],
        ]);

        return $builderDescriptionGroup;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Series\Category\ProductSeriesCategory|null $productSeriesCategory
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createSeoGroup(FormBuilderInterface $builder, ?ProductSeriesCategory $productSeriesCategory): FormBuilderInterface
    {
        $seoTitlesOptionsByDomainId = [];
        $seoMetaDescriptionsOptionsByDomainId = [];
        $seoH1OptionsByDomainId = [];
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();
            $seoTitlesOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->getTitlePlaceholder($locale, $productSeriesCategory),
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
        if ($productSeriesCategory) {
            $builderSeoGroup->add('url', UrlListType::class, [
                'route_name' => 'front_productseriescategory_detail',
                'entity_id' => $productSeriesCategory->getId(),
                'label' => t('URL settings'),
            ]);
        }
        return $builderSeoGroup;
    }

    /**
     * @param string $locale
     * @param \App\Model\Product\Series\Category\ProductSeriesCategory|null $productSeriesCategory
     * @return string
     */
    private function getTitlePlaceholder(string $locale, ?ProductSeriesCategory $productSeriesCategory = null): string
    {
        return $productSeriesCategory !== null ? $productSeriesCategory->getName($locale) : '';
    }
}
