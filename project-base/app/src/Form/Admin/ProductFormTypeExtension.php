<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Form\Constraints\UniqueProductCatnum;
use App\Model\Product\Flag\FlagFacade;
use App\Model\Product\Product;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints;

class ProductFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        private FormBuilderHelper $formBuilderHelper,
        private FlagFacade $flagFacade,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var \App\Model\Product\Product|null $product */
        $product = $options['product'];

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

        $catnumAttributes = $builder->get('basicInformationGroup')->get('catnum')->getAttributes();
        $builder->get('basicInformationGroup')->remove('catnum');
        $builder->get('basicInformationGroup')->add('catnum', TextType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(),
                new Constraints\Length(['max' => 100, 'maxMessage' => 'Catalog number cannot be longer than {{ limit }} characters']),
                new UniqueProductCatnum(['product' => $product]),
            ],
            'disabled' => $this->isProductMainVariant($product),
            'attr' => array_merge(
                $catnumAttributes,
                [
                    'data-unique-catnum-url' => $this->urlGenerator->generate('admin_product_catnumexists'),
                    'data-current-product-catnum' => $product !== null ? $product->getCatnum() : '',
                ],
            ),
            'label' => t('Catalog number'),
            'position' => ['before' => 'partno'],
        ]);

        $this->setBasicInformationGroup($builder);
        $this->setSeoGroup($builder);
        $this->setShortDescriptionsUspGroup($builder);
        $this->setStocksGroup($builder);
        $this->setStoresGroup($builder);
        $this->setDisplayAvailabilityGroup($builder, $product);
        $this->setPricesGroup($builder, $product);
        $this->setTransferredFilesGroup($builder, $product);
        $this->setRelatedProductsGroup($builder, $product);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setBasicInformationGroup(FormBuilderInterface $builder): void
    {
        $groupBuilder = $builder->get('basicInformationGroup');

        $groupBuilder
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
            ])
            ->add('weight', IntegerType::class, [
                'label' => t('Weight (g)'),
                'required' => false,
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
            ->add('sellingDenied', YesNoType::class, [
                'required' => false,
                'label' => t('Vyřadit z prodeje v celém eshopu'),
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t('Products excluded from sale can\'t be displayed on lists and can\'t be searched. Product detail is available by direct access from the URL, but it is not possible to add product to cart.'),
                ],
            ])
            ->add('saleExclusion', MultidomainType::class, [
                'label' => t('Vyřazení z prodeje dle domén'),
                'required' => false,
                'entry_type' => YesNoType::class,
                'position' => ['after' => 'sellingDenied'],
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
        if ($this->isProductMainVariant($product)) {
            $builderPricesGroup->remove('disabledPricesOnMainVariant');
        }
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setShortDescriptionsUspGroup(FormBuilderInterface $builder): void
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
    private function setStocksGroup(FormBuilderInterface $builder): void
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
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setStoresGroup(FormBuilderInterface $builder): void
    {
        $storeGroupBuilder = $builder->create('storesGroup', GroupType::class, [
            'label' => t('Exposed in Stores'),
        ]);

        $storeGroupBuilder->add('productStoreData', CollectionType::class, [
            'required' => false,
            'entry_type' => StoreProductFormType::class,
            'render_form_row' => false,
        ]);

        $builder->add($storeGroupBuilder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Product|null $product
     */
    private function setRelatedProductsGroup(FormBuilderInterface $builder, ?Product $product): void
    {
        if (!($product !== null && $product->isVariant())) {
            $relatedProductsGroupBuilder = $builder
                ->create('relatedProducts', ProductsType::class, [
                    'required' => false,
                    'main_product' => $product,
                    'label' => t('Related products'),
                    'allow_variants' => false,
                ]);

            $builder->add($relatedProductsGroupBuilder);
        }
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
    private function isProductMainVariant(?Product $product): bool
    {
        return $product !== null && $product->isMainVariant();
    }
}
