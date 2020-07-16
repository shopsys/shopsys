<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\Product\Flag\FlagFacade;
use App\Model\Product\Product;
use App\Model\Product\Type\ProductTypeFacade;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\DisplayOnlyUrlType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class ProductFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [
        'descriptions',
        'catnum',
        'ean',
        'name',
        'namePrefix',
        'nameSufix',
        'descriptions',
        'shortDescriptionUsp1',
        'shortDescriptionUsp2',
        'shortDescriptionUsp3',
        'shortDescriptionUsp4',
        'shortDescriptionUsp5',
        'pricesGroup',
        'categoriesByDomainId',
        'transferredFilesGroup',
        'productTypePlanFileUrl',
        'assemblyInstructionFileUrl',
        'productType',
        'accessories',
        'preorder',
        'saleExclusion',
        'vendorDeliveryDate',
        'flags',
        'images',
        'mountingState',
        'embeddedAccessories',
        'packageNotIncluded',
        'packagingUnit',
        'countPackages',
        'totalPackageWeight',
        'urls',
        'sellingPriceWithVat',
        'stockProductData',
    ];

    /**
     * @var \App\Component\Form\FormBuilderHelper
     */
    private $formBuilderHelper;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    /**
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    private $productTypeFacade;

    /**
     * @var \App\Model\Product\Flag\FlagFacade
     */
    private $flagFacade;

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     */
    public function __construct(
        FormBuilderHelper $formBuilderHelper,
        VatFacade $vatFacade,
        Domain $domain,
        ProductTypeFacade $productTypeFacade,
        FlagFacade $flagFacade
    ) {
        $this->formBuilderHelper = $formBuilderHelper;
        $this->domain = $domain;
        $this->vatFacade = $vatFacade;
        $this->productTypeFacade = $productTypeFacade;
        $this->flagFacade = $flagFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $product = $options['product'];
        /* @var $product \App\Model\Product\Product|null */

        $builder->add('namePrefix', LocalizedFullWidthType::class, [
            'required' => false,
            'entry_options' => [
                'constraints' => [
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Product prefix name cannot be longer than {{ limit }} characters']),
                ],
            ],
            'label' => t('Název prefix'),
            'render_form_row' => false,
            'position' => ['before' => 'name'],
        ]);

        $builder->add('nameSufix', LocalizedFullWidthType::class, [
            'required' => false,
            'entry_options' => [
                'constraints' => [
                    new Constraints\Length(['max' => 255, 'maxMessage' => 'Product suffix name cannot be longer than {{ limit }} characters']),
                ],
            ],
            'label' => t('Název suffix'),
            'render_form_row' => false,
            'position' => ['after' => 'name'],
        ]);

        $this->setVariantGroup($builder, $product);
        $this->setBasicInformationGroup($builder);
        $this->setSeoGroup($builder);
        $this->setShortDescriptionsUspGroup($builder, $options);
        $this->setStocksGroup($builder);
        $this->setDisplayAvailabilityGroup($builder, $product);
        $this->setPricesGroup($builder, $product);
        $this->setTransferredFilesGroup($builder, $product);
        $this->setPackagesGroup($builder);

        $builder->remove('parametersGroup');

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Product|null $product
     */
    private function setVariantGroup(FormBuilderInterface $builder, ?Product $product)
    {
        if ($this->isProductMainVariant($product)) {
            $variantGroup = $builder->get('variantGroup');

            $variantGroup->add('defaultVariantUrl', DisplayOnlyUrlType::class, [
                'label' => t('Defaultní variant varianta produktu'),
                'route' => 'admin_product_edit',
                'route_params' => [
                    'id' => $product->getDefaultVariant()->getId(),
                ],
                'route_label' => sprintf('%s (catcum: %s)', $product->getDefaultVariant()->getName(), $product->getDefaultVariant()->getCatnum()),
            ]);

            $variantGroup->add('variantParameters', CollectionType::class, [
                'required' => false,
                'disabled' => true,
                'label' => t('Parametry variant'),
                'label_attr' => ['style' => 'display: none;'],
                'entry_type' => TextType::class,
                'entry_options' => [
                    'disabled' => true,
                ],
            ]);

            $variantGroup->get('variantParameters')->addModelTransformer(new CallbackTransformer(
                /**
                 * @param \App\Model\Product\Parameter\Parameter[] $variantParameters
                 * @return string[]
                 */
                function (array $variantParameters) {
                    $counter = 1;
                    $parameterNames = [];
                    foreach ($variantParameters as $variantParameter) {
                        $key = t('Parametr_%counter%', ['%counter%' => $counter++]);
                        $parameterNames[$key] = $variantParameter->getName();
                    }
                    return $parameterNames;
                },
                function ($parameterNames) {
                    return $parameterNames;
                }
            ));
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setPackagesGroup(FormBuilderInterface $builder): void
    {
        $groupBuilder = $builder->create('packagesGroup', GroupType::class, [
            'label' => t('Informace o balení'),
            'position' => 'last',
        ]);

        $groupBuilder->add('mountingState', MultidomainType::class, [
                'required' => false,
                'entry_type' => YesNoType::class,
                'label' => t('Smontováno'),
            ])
            ->add('embeddedAccessories', MultidomainType::class, [
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'required' => false,
                ],
                'label' => t('Dodávané příslušenství'),
            ])
            ->add('packageNotIncluded', MultidomainType::class, [
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'required' => false,
                ],
                'label' => t('Neobsaženo v balení'),
            ])
            ->add('packagingUnit', MultidomainType::class, [
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'required' => false,
                ],
                'label' => t('Počet produktů v balení'),
            ])
            ->add('countPackages', MultidomainType::class, [
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'required' => false,
                ],
                'label' => t('Počet balíků'),
            ])
            ->add('totalPackageWeight', MultidomainType::class, [
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'required' => false,
                ],
                'label' => t('Celková váha balení'),
            ]);

        $builder->add($groupBuilder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setBasicInformationGroup(FormBuilderInterface $builder): void
    {
        $groupBuilder = $builder->get('basicInformationGroup');

        $groupBuilder->add('productType', MultidomainType::class, [
                'required' => true,
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'required' => true,
                    'choices' => $this->productTypeFacade->getAll(),
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'constraints' => [
                        new Constraints\NotBlank([
                            'message' => 'Prosím vyberte typ',
                        ]),
                    ],
                ],
                'label' => t('Typ'),
            ])

            ->add('flags', MultidomainType::class, [
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => 'input--full-width',
                    ],
                    'choices' => $this->flagFacade->getAll(),
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'multiple' => true,
                    'expanded' => true,
                ],
                'required' => false,
                'display_format' => FormRenderingConfigurationExtension::DISPLAY_FORMAT_MULTIDOMAIN_ROWS_NO_PADDING,
                'label' => t('Flags'),
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Product|null $product
     */
    private function setDisplayAvailabilityGroup(FormBuilderInterface $builder, ?Product $product): void
    {
        $groupBuilder = $builder->get('displayAvailabilityGroup');
        $groupBuilder->remove('availability');
        $groupBuilder->remove('orderingPriority');

        $groupBuilder->get('stockGroup')
            ->remove('stockQuantity')
            ->remove('outOfStockAction')
            ->remove('outOfStockAvailability');

        $groupBuilder
            ->add('preorder', YesNoType::class, [
                'required' => false,
                'disabled' => $this->isProductMainVariant($product),
                'label' => 'Povolit nákup do mínusu',
            ])
            ->add('saleExclusion', MultidomainType::class, [
                'label' => t('Vyřazení z prodeje'),
                'required' => false,
                'entry_type' => YesNoType::class,
            ])
            ->add('vendorDeliveryDate', TextType::class, [
                'required' => false,
                'label' => 'Dodací lhůta dodavatele',
            ])
            ->add('usingStock', YesNoType::class, [
                'data' => true,
                'required' => false,
                'disabled' => true,
                'label' => t('Use stocks'),
            ])
            ->add('domainHidden', MultidomainType::class, [
                'label' => t('Skrýt na doméně'),
                'required' => false,
                'entry_type' => YesNoType::class,
                'position' => ['after' => 'hidden'],
            ])
            ->add('domainOrderingPriority', MultidomainType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'required' => true,
                ],
                'label' => t('Sorting priority'),
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Product|null $product
     */
    private function setTransferredFilesGroup(FormBuilderInterface $builder, ?Product $product): void
    {
        if ($product === null) {
            return;
        }

        $groupBuilder = $builder->create('transferredFilesGroup', GroupType::class, [
            'label' => t('Přenesené soubory'),
        ]);

        $groupBuilder->add('assemblyInstructionFileUrl', MultidomainType::class, [
            'label' => t('Instalační manuál'),
            'required' => false,
            'entry_type' => UrlType::class,
        ]);

        $groupBuilder->add('productTypePlanFileUrl', MultidomainType::class, [
            'label' => t('Typový plán'),
            'required' => false,
            'entry_type' => UrlType::class,
        ]);

        $builder->add($groupBuilder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Product|null $product
     */
    private function setPricesGroup(FormBuilderInterface $builder, ?Product $product): void
    {
        $builderPricesGroup = $builder->get('pricesGroup');
        $builderPricesGroup->remove('productCalculatedPricesGroup');
        if ($this->isProductMainVariant($product)) {
            $builderPricesGroup->remove('disabledPricesOnMainVariant');
        }

        $builderPricesGroup->add('lowPriceWithVat', MultidomainType::class, [
                'label' => t('Nižší cena s DPH'),
                'entry_type' => MoneyType::class,
                'entry_options' => [
                    'scale' => 6,
                ],
                'required' => false,
            ])
            ->add('lowPriceWithoutVat', MultidomainType::class, [
                'label' => t('Nižší cena bez DPH'),
                'entry_type' => MoneyType::class,
                'entry_options' => [
                    'scale' => 6,
                ],
                'required' => false,
            ])
            ->add('lowPriceWithoutVat', MultidomainType::class, [
                'label' => t('Nízká cena bez DPH'),
                'entry_type' => MoneyType::class,
                'entry_options' => [
                    'scale' => 6,
                ],
                'required' => false,
            ])
            ->add('highPriceWithVat', MultidomainType::class, [
                'label' => t('Vyšší cena s DPH'),
                'entry_type' => MoneyType::class,
                'entry_options' => [
                    'required' => true,
                    'scale' => 6,
                ],
                'required' => true,
            ])
            ->add('highPriceWithoutVat', MultidomainType::class, [
                'label' => t('Vyšší cena bez DPH'),
                'entry_type' => MoneyType::class,
                'entry_options' => [
                    'required' => true,
                    'scale' => 6,
                ],
                'required' => true,
            ])
            ->add('sellingPriceWithVat', MultidomainType::class, [
                'label' => t('Prodejní cena s DPH'),
                'entry_type' => MoneyType::class,
                'entry_options' => [
                    'scale' => 6,
                ],
                'required' => false,
            ])
            ->add('highPriceWithoutVat', MultidomainType::class, [
                'label' => t('Vysoká cena bez DPH'),
                'entry_type' => MoneyType::class,
                'entry_options' => [
                    'scale' => 6,
                ],
                'required' => false,
            ]);

        $vatsIndexedByDomainId = $builder->create('vatsIndexedByDomainId', FormType::class, [
            'compound' => true,
            'render_form_row' => false,
            'disabled' => $this->isProductMainVariant($product),
        ]);

        foreach ($this->domain->getAll() as $domainConfig) {
            $vatsIndexedByDomainId
                ->add($domainConfig->getId(), ChoiceType::class, [
                    'required' => true,
                    'disabled' => true,
                    'choices' => $this->vatFacade->getAllForDomainIncludingMarkedForDeletion($domainConfig->getId()),
                    'choice_label' => 'name',
                    'choice_value' => 'id',
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter VAT rate']),
                    ],
                    'label' => t('DPH {{domainName}}', ['domainName' => $domainConfig->getName()]),
                ]);
        }

        $builderPricesGroup->add($vatsIndexedByDomainId);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    private function setShortDescriptionsUspGroup(FormBuilderInterface $builder, array $options): void
    {
        $builderShortDescriptionsUspGroup = $builder->create('shortDescriptionsUspGroups', GroupType::class, [
            'label' => t('Krátký popis USP'),
        ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp1', MultidomainType::class, [
                'label' => t('Krátký popis 1'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp2', MultidomainType::class, [
                'label' => t('Krátký popis 2'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp3', MultidomainType::class, [
                'label' => t('Krátký popis 3'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp4', MultidomainType::class, [
                'label' => t('Krátký popis 4'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp5', MultidomainType::class, [
                'label' => t('Krátký popis 5'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builder->add($builderShortDescriptionsUspGroup);

        /** @var \Ivory\OrderedForm\Builder\OrderedFormBuilder $shortDescriptionsUspGroups */
        $shortDescriptionsUspGroups = $builder->get('shortDescriptionsUspGroups');
        $shortDescriptionsUspGroups->setPosition(['after' => 'shortDescriptionsGroup']);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setSeoGroup(FormBuilderInterface $builder): void
    {
        $builderSeoGroup = $builder->get('seoGroup');

        $builderSeoGroup->remove('seoH1s');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setStocksGroup(FormBuilderInterface $builder)
    {
        $stockGroupBuilder = $builder->create('stocksGroup', GroupType::class, [
            'label' => t('Stocks'),
        ]);

        $stockGroupBuilder->add('stockProductData', CollectionType::class, [
            'required' => false,
            'entry_type' => StockProductFormType::class,
            'render_form_row' => false,
        ]);

        $builder->add($stockGroupBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield ProductFormType::class;
    }

    /**
     * @param \App\Model\Product\Product|null $product
     * @return bool
     */
    private function isProductMainVariant(?Product $product)
    {
        return $product !== null && $product->isMainVariant();
    }
}
