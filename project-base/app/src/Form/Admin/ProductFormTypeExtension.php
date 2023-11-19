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
            'label' => t('Name prefix'),
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
            'label' => t('Name suffix'),
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
        $this->setDisplayAvailabilityGroup($builder, $product);
        $this->setPricesGroup($builder, $product);
        $this->setTransferredFilesGroup($builder, $product);
        $this->setRelatedProductsGroup($builder, $product);
        $this->setVideoGroup($builder);

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
                'label' => t('Allow overselling'),
            ])
            ->add('sellingDenied', YesNoType::class, [
                'required' => false,
                'label' => t('Exclude from sale on whole eshop'),
                'attr' => [
                    'icon' => true,
                    'iconTitle' => t('Products excluded from sale can\'t be displayed on lists and can\'t be searched. Product detail is available by direct access from the URL, but it is not possible to add product to cart.'),
                ],
            ])
            ->add('saleExclusion', MultidomainType::class, [
                'label' => t('Exclude from sale on domains'),
                'required' => false,
                'entry_type' => YesNoType::class,
                'position' => ['after' => 'sellingDenied'],
            ])
            ->add('vendorDeliveryDate', TextType::class, [
                'required' => false,
                'label' => t('Supplier\'s delivery time'),
                'constraints' => [
                    new Constraints\Type(['type' => 'numeric', 'message' => 'Supplier\'s delivery time must be a number']),
                    new Constraints\GreaterThanOrEqual(['value' => 0, 'message' => 'Supplier\'s delivery time must be 0 or more']),
                ],

            ])
            ->add('usingStock', YesNoType::class, [
                'data' => true,
                'required' => false,
                'disabled' => true,
                'label' => t('Use stocks'),
            ])
            ->add('domainHidden', MultidomainType::class, [
                'label' => t('Hide on domain'),
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
            'label' => t('Transferred files'),
        ]);

        $groupBuilder->add('assemblyInstructionFileUrl', MultidomainType::class, [
            'label' => t('Installation manual'),
            'required' => false,
            'entry_type' => UrlType::class,
        ]);

        $groupBuilder->add('productTypePlanFileUrl', MultidomainType::class, [
            'label' => t('Type plan'),
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
            'label' => t('Short description USP'),
        ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp1', MultidomainType::class, [
                'label' => t('Short description 1'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp2', MultidomainType::class, [
                'label' => t('Short description 2'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp3', MultidomainType::class, [
                'label' => t('Short description 3'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp4', MultidomainType::class, [
                'label' => t('Short description 4'),
                'entry_type' => TextType::class,
                'required' => false,
            ]);

        $builderShortDescriptionsUspGroup
            ->add('shortDescriptionUsp5', MultidomainType::class, [
                'label' => t('Short description 5'),
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
            'label' => t('Warehouses'),
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
    private function setVideoGroup(FormBuilderInterface $builder): void
    {
        $videosGroup = $builder->create('videosGroup', GroupType::class, [
            'label' => t('Videos'),
        ]);
        $videosGroup
            ->add(
                $builder->create('productVideosData', CollectionType::class, [
                    'entry_type' => VideoTokenType::class,
                    'render_form_row' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'label' => false,
                    'required' => false,
                ]),
            );

        $builder->add($videosGroup);
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
