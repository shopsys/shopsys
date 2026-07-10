<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ProductParameterValueFormType;
use Shopsys\FrameworkBundle\Form\Admin\Product\Price\ProductPricesWithVatSelectType;
use Shopsys\FrameworkBundle\Form\Admin\Stock\ProductStockFormType;
use Shopsys\FrameworkBundle\Form\CategoriesType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueProductCatnum;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueProductParameters;
use Shopsys\FrameworkBundle\Form\DatePickerType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyUrlType;
use Shopsys\FrameworkBundle\Form\FileUploadType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Shopsys\FrameworkBundle\Form\ProductParameterValueType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\Transformers\ProductParameterValueToProductParameterValuesLocalizedTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Form\UrlListType;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints;

final class ProductFormType extends AbstractType
{
    public const string CSRF_TOKEN_ID = 'product_edit_type';

    public function __construct(
        private readonly BrandFacade $brandFacade,
        private readonly FlagFacade $flagFacade,
        private readonly UnitFacade $unitFacade,
        private readonly Domain $domain,
        private readonly SeoSettingFacade $seoSettingFacade,
        private readonly RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer,
        private readonly PluginCrudExtensionFacade $pluginDataFormExtensionFacade,
        private readonly ProductParameterValueToProductParameterValuesLocalizedTransformer $productParameterValueToProductParameterValuesLocalizedTransformer,
        private readonly ProductFacade $productFacade,
        private readonly TransportFacade $transportFacade,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ProductTypeEnum $productTypeEnum,
        private readonly SpecialPriceFacade $specialPriceFacade,
        private readonly ClockInterface $clock,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product|null $product */
        $product = $options['product'];

        $disabledItemInMainVariantHelp = [];

        if ($this->isProductMainVariant($product)) {
            $disabledItemInMainVariantHelp = [
                'help' => t('This item can be set in product detail of a specific variant'),
            ];
        }

        $builder
            ->add('namePrefix', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(max: 255, maxMessage: 'Product prefix name cannot be longer than {{ limit }} characters'),
                    ],
                ],
                'label' => 'Name prefix',
                'display_mode' => 'columns',
            ])
            ->add('name', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Product name cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
                'label' => 'Name',
                'display_mode' => 'columns',
            ])
            ->add('nameSuffix', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(max: 255, maxMessage: 'Product suffix name cannot be longer than {{ limit }} characters'),
                    ],
                ],
                'label' => 'Name suffix',
                'display_mode' => 'columns',
            ]);

        if ($this->isProductVariant($product) || $this->isProductMainVariant($product)) {
            $builder->add($this->createVariantGroup($builder, $product));
        }

        $builder->add($this->createBasicInformationGroup($builder, $product, $disabledItemInMainVariantHelp));
        $builder->add($this->createDisplayAvailabilityGroup($builder, $product));
        $builder->add($this->createPricesGroup($builder, $product));
        $builder->add($this->createPromotionGroup($builder, $product));
        $builder->add($this->createStocksGroup($builder, $product));
        $builder->add($this->createDescriptionsGroup($builder, $product));
        $builder->add($this->createShortDescriptionsGroup($builder, $product));
        $builder->add($this->createShortDescriptionsUspGroup($builder));
        $builder->add($this->createParametersGroup($builder));
        $builder->add($this->createSeoGroup($builder, $product));
        $builder->add($this->createImagesGroup($builder, $options));
        $builder->add($this->createFilesGroup($builder, $options));
        $builder->add($this->createAccessoriesGroup($builder, $product));

        if (!$this->isProductVariant($product)) {
            $builder->add($this->createRelatedProductsGroup($builder, $product));
        }
        $builder->add($this->createVideosGroup($builder));
        $actionBarOptions = [
            'back_route' => 'admin_product_list',
            'entity' => $product,
        ];

        if ($product !== null) {
            $actionBarOptions['entity_name'] = $product->getName();
            $actionBarOptions['entity_identifier'] = $product->getCatnum();
        }

        $builder->add('actionBar', ActionBarType::class, $actionBarOptions);

        $this->pluginDataFormExtensionFacade->extendForm($builder, 'product', 'pluginData');
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('product')
            ->setAllowedTypes('product', [Product::class, 'null'])
            ->setDefaults([
                'data_class' => ProductData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'csrf_token_id' => self::CSRF_TOKEN_ID,
            ]);
    }

    private function createBasicInformationGroup(
        FormBuilderInterface $builder,
        ?Product $product,
        array $disabledItemInMainVariantHelp = [],
    ): FormBuilderInterface {
        $builderBasicInformationGroup = $builder->create('basicInformationGroup', GroupType::class, [
            'label' => 'Basic information',
        ]);

        if (!$this->isProductMainVariant($product)) {
            $builderBasicInformationGroup->add('productType', ChoiceType::class, [
                'required' => true,
                'choices' => $this->productTypeEnum->getAllIndexedByTranslations(),
                'label' => 'Product type',
                'help' => t('For the electronic gift voucher type, stock levels are ignored and the product is always available.'),
            ]);
        }

        $builderBasicInformationGroup->add('catnum', TextType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(),
                new Constraints\Length(max: 100, maxMessage: 'Catalog number cannot be longer than {{ limit }} characters'),
                new UniqueProductCatnum(product: $product),
            ],
            'disabled' => $this->isProductMainVariant($product),
            'attr' => [
                'data-unique-catnum-url' => $this->urlGenerator->generate('admin_product_catnumexists'),
                'data-current-product-catnum' => $product !== null ? $product->getCatnum() : '',
            ],
            'label' => 'Catalog number',
            ...$disabledItemInMainVariantHelp,
        ])
            ->add('partno', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'Part number cannot be longer than {{ limit }} characters',
                    ),
                ],
                'disabled' => $this->isProductMainVariant($product),
                'label' => 'PartNo (serial number)',
                ...$disabledItemInMainVariantHelp,
            ])
            ->add('ean', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Constraints\Length(
                        max: 100,
                        maxMessage: 'EAN cannot be longer than {{ limit }} characters',
                    ),
                ],
                'disabled' => $this->isProductMainVariant($product),
                'label' => 'EAN',
                ...$disabledItemInMainVariantHelp,
            ]);

        if ($product !== null) {
            $builderBasicInformationGroup->add('id', DisplayOnlyType::class, [
                'label' => 'ID',
                'data' => $product->getId(),
            ]);
        }

        $flagsIdsWithPromotionXy = $this->flagFacade->getFlagsIdsWithPromotionXy();

        $builderBasicInformationGroup
            ->add('flagsByDomainId', MultidomainType::class, [
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'choices' => $this->flagFacade->getAll(),
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'choice_attr' => function ($flag) use ($flagsIdsWithPromotionXy) {
                        if (in_array($flag->getId(), $flagsIdsWithPromotionXy, true)) {
                            return [
                                'disabled' => 'disabled',
                            ];
                        }

                        return [];
                    },
                    'multiple' => true,
                    'expanded' => true,
                ],
                'required' => false,
                'label' => 'Flags',
                'display_mode' => 'columns',
                'row_attr' => [
                    'class' => 'mb-3 form-check-hoverable',
                ],
            ])
            ->add('brand', ChoiceType::class, [
                'required' => false,
                'choices' => $this->brandFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'placeholder' => '-- Choose brand --',
                'label' => 'Brand',
            ])
            ->add('weight', IntegerType::class, [
                'label' => 'Weight (g)',
                'required' => false,
            ])
            ->add('unit', ChoiceType::class, [
                'required' => true,
                'choices' => $this->unitFacade->getAll(),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please choose unit',
                    ),
                ],
                'label' => 'Unit',
            ])
            ->add('excludedTransports', ChoiceType::class, [
                'required' => false,
                'choices' => $this->transportFacade->getAll(),
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'label' => 'Excluded transports',
            ])
            ->add('personalPickupOnly', YesNoType::class, [
                'required' => false,
                'label' => 'Personal pickup only',
                'help' => t('If enabled, a cart containing this product can only use personal pickup at store transports.'),
            ]);

        return $builderBasicInformationGroup;
    }

    private function createShortDescriptionsGroup(
        FormBuilderInterface $builder,
        ?Product $product,
    ): FormBuilderInterface {
        $builderShortDescriptionGroup = $builder->create('shortDescriptionsGroup', GroupType::class, [
            'label' => 'Short description',
        ]);

        if ($this->isProductVariant($product)) {
            $builderShortDescriptionGroup->add('shortDescriptions', DisplayOnlyType::class, [
                'data' => t('Short description can be set in the main variant.'),
                'label' => false,
            ]);
        } else {
            $builderShortDescriptionGroup
                ->add('shortDescriptions', MultidomainType::class, [
                    'entry_type' => TextareaType::class,
                    'required' => false,
                    'disabled' => $this->isProductVariant($product),
                    'label' => false,
                ]);
        }

        return $builderShortDescriptionGroup;
    }

    private function createDescriptionsGroup(FormBuilderInterface $builder, ?Product $product): FormBuilderInterface
    {
        $builderDescriptionGroup = $builder->create('descriptionsGroup', GroupType::class, [
            'label' => 'Description',
        ]);

        if ($this->isProductVariant($product)) {
            $builderDescriptionGroup->add('descriptions', DisplayOnlyType::class, [
                'data' => t('Description can be set on product detail of the main product.'),
                'label' => false,
            ]);
        } else {
            $builderDescriptionGroup
                ->add('descriptions', MultidomainType::class, [
                    'entry_type' => CKEditorType::class,
                    'required' => false,
                    'disabled' => $this->isProductVariant($product),
                    'label' => false,
                ]);
        }

        return $builderDescriptionGroup;
    }

    private function createDisplayAvailabilityGroup(
        FormBuilderInterface $builder,
        ?Product $product,
    ): FormBuilderInterface {
        $categoriesOptionsByDomainId = [];

        foreach ($this->domain->getAdminEnabledDomainIds() as $domainId) {
            $categoriesOptionsByDomainId[$domainId] = [
                'domain_id' => $domainId,
                'product' => $product,
            ];
        }

        $builderDisplayAvailabilityGroup = $builder->create('displayAvailabilityGroup', GroupType::class, [
            'label' => 'Display and availability',
        ]);

        $builderDisplayAvailabilityGroup
            ->add('hidden', YesNoType::class, [
                'label' => 'Hide product',
            ]);

        $builderDisplayAvailabilityGroup->add('domainHidden', MultidomainType::class, [
            'label' => 'Hide on domain',
            'entry_type' => YesNoType::class,
            'display_mode' => 'columns',
            'row_attr' => [
                'class' => 'mb-3',
            ],
        ]);

        $builderDisplayAvailabilityGroup
            ->add('sellingFrom', DatePickerType::class, [
                'required' => false,
                'invalid_message' => 'Enter date in DD.MM.YYYY format',
                'label' => 'Selling start date',
                ...($product === null ? ['data' => $this->clock->now()] : []),
            ])
            ->add('sellingTo', DatePickerType::class, [
                'required' => false,
                'invalid_message' => 'Enter date in DD.MM.YYYY format',
                'label' => 'Selling end date',
            ])
            ->add('sellingDenied', YesNoType::class, [
                'label' => 'Exclude from sale on whole eshop',
                'help' => t(
                    'Products excluded from sale can\'t be displayed on lists and can\'t be searched. Product detail is available by direct access from the URL, but it is not possible to add product to cart.',
                ),
            ])
            ->add('domainSellingDenied', MultidomainType::class, [
                'label' => 'Exclude from sale on domains',
                'entry_type' => YesNoType::class,
                'display_mode' => 'columns',
            ]);

        if ($this->isProductVariant($product)) {
            $builderDisplayAvailabilityGroup
                ->add('categoriesByDomainId', DisplayOnlyType::class, [
                    'data' => t('You can set the categories on product detail of the main variant'),
                    'label' => 'Assign to category',
                    'row_attr' => [
                        'class' => 'mb-3',
                    ],
                ]);
        } else {
            $builderDisplayAvailabilityGroup
                ->add('categoriesByDomainId', MultidomainType::class, [
                    'required' => false,
                    'entry_type' => CategoriesType::class,
                    'options_by_domain_id' => $categoriesOptionsByDomainId,
                    'disabled' => $this->isProductVariant($product),
                    'label' => 'Assign to category',
                    'display_mode' => 'columns',
                    'row_attr' => [
                        'class' => 'mb-3',
                    ],
                ]);
        }
        $builderDisplayAvailabilityGroup
            ->add('orderingPriorityByDomainId', MultidomainType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'required' => true,
                ],
                'label' => 'Sorting priority',
                'display_mode' => 'columns',
            ]);

        return $builderDisplayAvailabilityGroup;
    }

    private function createStocksGroup(FormBuilderInterface $builder, ?Product $product): FormBuilderInterface
    {
        $stockGroupBuilder = $builder->create('stocksGroup', GroupType::class, [
            'label' => 'Warehouses',
        ]);

        if ($this->isProductMainVariant($product)) {
            $stockGroupBuilder
                ->add('productStockDataMessage', DisplayOnlyType::class, [
                    'label' => false,
                    'data' => t('The stock quantities are set for the product variants separately.'),
                ]);
        } else {
            $stockGroupBuilder->add('isAllowedNegativeStock', YesNoType::class, [
                'required' => false,
                'label' => 'Allow negative stock',
                'help' => t('If you allow negative stock, it is possible to order more items than are currently in stock.'),
            ]);

            $stockGroupBuilder->add('expectedRestockingDate', DatePickerType::class, [
                'required' => false,
                'invalid_message' => 'Enter date in DD.MM.YYYY format',
                'label' => 'Expected restocking date',
                'help' => t('If the product is out of stock, its availability is displayed as "Expecting [date]". A date in the past is ignored.'),
            ]);

            $stockGroupBuilder->add('productStockData', CollectionType::class, [
                'required' => false,
                'entry_type' => ProductStockFormType::class,
            ]);
        }

        return $stockGroupBuilder;
    }

    private function createPricesGroup(FormBuilderInterface $builder, ?Product $product): FormBuilderInterface
    {
        $builderPricesGroup = $builder->create('pricesGroup', GroupType::class, [
            'label' => 'Prices',
        ]);

        if ($this->isProductMainVariant($product)) {
            $builderPricesGroup->add('disabledPricesOnMainVariant', DisplayOnlyType::class, [
                'data' => t('You can set the prices on product detail of specific variant.'),
                'label' => false,
            ]);

            return $builderPricesGroup;
        }

        $productPricesIndexedByDomainId = null;

        if ($product !== null) {
            $productPricesIndexedByDomainId = $this->productFacade->getAllProductPricesIndexedByDomainId($product);
        }

        $optionsByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $optionsByDomainId[$domainId] = [
                'domain_id' => $domainId,
                'product_prices' => $productPricesIndexedByDomainId[$domainId] ?? null,
            ];
        }

        $builderPricesGroup->add(
            'productInputPricesByDomain',
            MultidomainType::class,
            [
                'label' => false,
                'entry_type' => ProductPricesWithVatSelectType::class,
                'options_by_domain_id' => $optionsByDomainId,
                'entry_options' => [
                    'required' => true,
                ],
                'required' => true,
            ],
        );

        if ($product !== null) {
            $priceListOverviewOptionsByDomainId = [];
            $isAnySpecialPrice = false;

            foreach ($this->domain->getAdminEnabledDomainIds() as $domainId) {
                $specialPrices = $this->specialPriceFacade->getCurrentAndFutureSpecialPrices($product, $domainId);
                $priceListOverviewOptionsByDomainId[$domainId] = [
                    'specialPrices' => $specialPrices,
                ];
                $isAnySpecialPrice = $isAnySpecialPrice || count($specialPrices) > 0;
            }

            if ($isAnySpecialPrice) {
                $builderPricesGroup->add(
                    'priceListOverview',
                    MultidomainType::class,
                    [
                        'label' => 'Price list overview',
                        'entry_type' => PriceListOverviewType::class,
                        'required' => false,
                        'mapped' => false,
                        'options_by_domain_id' => $priceListOverviewOptionsByDomainId,
                    ],
                );
            }
        }

        return $builderPricesGroup;
    }

    private function createPromotionGroup(FormBuilderInterface $builder, ?Product $product): FormBuilderInterface
    {
        $promotionGroup = $builder->create('promotionGroup', GroupType::class, [
            'label' => t('Promotion X + Y free'),
        ]);

        if ($this->isProductMainVariant($product)) {
            $promotionGroup->add('promotionInfo', DisplayOnlyType::class, [
                'data' => t('Promotion can be set on specific variant.'),
                'label' => false,
            ]);

            return $promotionGroup;
        }

        $promotionGroup->add('promotionXyData', MultidomainType::class, [
            'label' => false,
            'entry_type' => ProductPromotionXyType::class,
            'required' => false,
            'entry_options' => [
                'label' => false,
                'required' => false,
            ],
        ]);

        return $promotionGroup;
    }

    private function createSeoGroup(FormBuilderInterface $builder, ?Product $product): FormBuilderInterface
    {
        $seoTitlesOptionsByDomainId = [];
        $seoMetaDescriptionsOptionsByDomainId = [];
        $seoH1OptionsByDomainId = [];

        foreach ($this->domain->getAdminEnabledDomainIds() as $domainId) {
            $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

            $seoTitlesOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->getTitlePlaceholder($locale, $product),
                    'data-js-placeholder-source-input-id' => 'product_form_name_' . $locale,
                    'data-js-recommended-length' => 60,
                ],
            ];
            $seoMetaDescriptionsOptionsByDomainId[$domainId] = [
                'attr' => [
                    'placeholder' => $this->seoSettingFacade->getDescriptionMainPage($domainId),
                    'data-js-recommended-length' => 155,
                ],
            ];
            $seoH1OptionsByDomainId[$domainId] = $seoTitlesOptionsByDomainId[$domainId];
        }
        $builderSeoGroup = $builder->create('seoGroup', GroupType::class, [
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
                'entry_type' => TextType::class,
                'required' => false,
                'options_by_domain_id' => $seoH1OptionsByDomainId,
                'label' => 'Heading (H1)',
            ]);

        if ($product) {
            $builderSeoGroup->add('urls', UrlListType::class, [
                'route_name' => 'front_product_detail',
                'entity_id' => $product->getId(),
                'label' => 'URL settings',
            ]);
        }

        return $builderSeoGroup;
    }

    private function createVariantGroup(FormBuilderInterface $builder, ?Product $product): FormBuilderInterface
    {
        $variantGroup = $builder->create('variantGroup', FormType::class, [
            'inherit_data' => true,
            'label' => false,
            'row_attr' => ['class' => 'mt-xl-3'],
        ]);

        if ($this->isProductVariant($product)) {
            $variantGroup->add('mainVariantUrl', DisplayOnlyUrlType::class, [
                'label' => 'Product is variant',
                'route' => 'admin_product_edit',
                'route_params' => [
                    'id' => $product->getMainVariant()->getId(),
                ],
                'route_label' => $product->getMainVariant()->getName(),
            ]);

            $variantGroup->add('variantAlias', LocalizedType::class, [
                'required' => false,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\Length(
                            max: 255,
                            maxMessage: 'Variant alias cannot be longer than {{ limit }} characters',
                        ),
                    ],
                ],
                'label' => 'Variant alias',
            ]);
        }

        if ($this->isProductMainVariant($product)) {
            $variantGroup->add('variants', ProductsType::class, [
                'required' => false,
                'main_product' => $product,
                'allow_main_variants' => false,
                'allow_variants' => false,
                'label_button_add' => t('Add variant'),
                'label' => 'Variants',
                'top_info_title' => t('Product is main variant.'),
            ]);
        }

        return $variantGroup;
    }

    private function getTitlePlaceholder(string $locale, ?Product $product = null): string
    {
        return $product?->getName($locale) ?? '';
    }

    private function isProductMainVariant(?Product $product): bool
    {
        return $product !== null && $product->isMainVariant();
    }

    private function isProductVariant(?Product $product): bool
    {
        return $product !== null && $product->isVariant();
    }

    private function createParametersGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderParametersGroup = $builder->create('parametersGroup', GroupType::class, [
            'label' => 'Parameters',
        ]);

        $builderParametersGroup
            ->add($builder->create('parameters', ProductParameterValueType::class, [
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => ProductParameterValueFormType::class,
                'constraints' => [
                    new UniqueProductParameters(
                        message: 'Parameter {{ parameterName }} is used more than once',
                    ),
                ],
                'error_bubbling' => false,
                'label' => false,
            ])
                ->addModelTransformer($this->productParameterValueToProductParameterValuesLocalizedTransformer));

        return $builderParametersGroup;
    }

    private function createImagesGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $builderImageGroup = $builder->create('imageGroup', GroupType::class, [
            'label' => 'Images',
        ]);
        $builderImageGroup
            ->add('images', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Product::class,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded image is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $options['product'],
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
                'label' => 'Images',
            ]);

        return $builderImageGroup;
    }

    private function createAccessoriesGroup(FormBuilderInterface $builder, ?Product $product): FormBuilderInterface
    {
        return $builder
            ->create('accessories', ProductsType::class, [
                'required' => false,
                'main_product' => $product,
                'sortable' => true,
                'label' => 'Accessories',
            ])
            ->addViewTransformer($this->removeDuplicatesTransformer);
    }

    private function createRelatedProductsGroup(FormBuilderInterface $builder, ?Product $product): FormBuilderInterface
    {
        return $builder
            ->create('relatedProducts', ProductsType::class, [
                'required' => false,
                'main_product' => $product,
                'label' => t('Related products'),
            ])
            ->addViewTransformer($this->removeDuplicatesTransformer);
    }

    private function createShortDescriptionsUspGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $builderShortDescriptionsUspGroup = $builder->create('shortDescriptionsUspGroups', GroupType::class, [
            'label' => 'Short description USP',
        ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp1ByDomainId', MultidomainType::class, [
                'label' => t('Short description %number%', ['%number%' => 1]),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp2ByDomainId', MultidomainType::class, [
                'label' => t('Short description %number%', ['%number%' => 2]),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp3ByDomainId', MultidomainType::class, [
                'label' => t('Short description %number%', ['%number%' => 3]),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp4ByDomainId', MultidomainType::class, [
                'label' => t('Short description %number%', ['%number%' => 4]),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp5ByDomainId', MultidomainType::class, [
                'label' => t('Short description %number%', ['%number%' => 5]),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        return $builderShortDescriptionsUspGroup;
    }

    private function createFilesGroup(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $builderFileGroup = $builder->create('fileGroup', GroupType::class, [
            'label' => 'Files',
        ]);

        $builderFileGroup
            ->add('files', FileUploadType::class, [
                'required' => false,
                'file_entity_class' => Product::class,
                'file_constraints' => [
                    new Constraints\File(
                        maxSize: '2M',
                        maxSizeMessage: 'Uploaded file is too large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an file is {{ limit }} {{ suffix }}.',
                    ),
                ],
                'entity' => $options['product'],
                'label' => 'Files',
            ]);

        return $builderFileGroup;
    }

    private function createVideosGroup(FormBuilderInterface $builder): FormBuilderInterface
    {
        $videosGroup = $builder->create('videosGroup', GroupType::class, [
            'label' => 'Videos',
        ]);
        $videosGroup
            ->add(
                $builder->create('productVideosData', CollectionType::class, [
                    'entry_type' => VideoTokenType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => false,
                    'required' => false,
                ]),
            );

        return $videosGroup;
    }
}
