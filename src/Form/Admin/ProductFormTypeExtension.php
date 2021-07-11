<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\Product\Flag\FlagFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductFacade;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\FormRenderingConfigurationExtension;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @var \App\Component\Form\FormBuilderHelper
     */
    private $formBuilderHelper;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    /**
     * @var \App\Model\Product\Flag\FlagFacade
     */
    private $flagFacade;

    /**
     * @var \App\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \App\Model\Product\Product|null
     */
    private $product;

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        FormBuilderHelper $formBuilderHelper,
        VatFacade $vatFacade,
        Domain $domain,
        FlagFacade $flagFacade,
        ProductFacade $productFacade
    ) {
        $this->formBuilderHelper = $formBuilderHelper;
        $this->domain = $domain;
        $this->vatFacade = $vatFacade;
        $this->flagFacade = $flagFacade;
        $this->productFacade = $productFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->product = $options['product'];

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
                new Constraints\Callback([$this, 'validateUniqueCatnum']),
            ],
            'disabled' => $this->isProductMainVariant($this->product),
            'attr' => $catnumAttributes,
            'label' => t('Catalog number'),
            'position' => ['before' => 'partno'],
        ]);

        $this->setBasicInformationGroup($builder);
        $this->setSeoGroup($builder);
        $this->setShortDescriptionsUspGroup($builder, $options);
        $this->setStocksGroup($builder);
        $this->setStoresGroup($builder);
        $this->setDisplayAvailabilityGroup($builder, $this->product);
        $this->setPricesGroup($builder, $this->product);
        $this->setTransferredFilesGroup($builder, $this->product);

        $builder->remove('parametersGroup');

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
    }

    /**
     * @param string|null $catnum
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateUniqueCatnum($catnum, ExecutionContextInterface $context)
    {
        if ($catnum === null) {
            return;
        }

        $productByCatnum = $this->productFacade->findByCatnum($catnum);

        if ($this->product === null && $productByCatnum !== null) {
            $context->addViolation(t('Produkt s tímto katalogovým číslem již existuje'));
        }

        if ($this->product === null || $catnum === $this->product->getCatnum()) {
            return;
        }

        if ($productByCatnum !== null) {
            $context->addViolation(t('Produkt s tímto katalogovým číslem již existuje'));
        }
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

        $vatsIndexedByDomainId = $builder->create('vatsIndexedByDomainId', FormType::class, [
            'compound' => true,
            'render_form_row' => false,
            'disabled' => $this->isProductMainVariant($product),
        ]);

        foreach ($this->domain->getAll() as $domainConfig) {
            $vatsIndexedByDomainId
                ->add((string)$domainConfig->getId(), ChoiceType::class, [
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
