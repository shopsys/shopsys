<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\Product\Product;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Symfony\Component\Form\AbstractTypeExtension;
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
    ];

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
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(FormBuilderHelper $formBuilderHelper, VatFacade $vatFacade, Domain $domain)
    {
        $this->formBuilderHelper = $formBuilderHelper;
        $this->domain = $domain;
        $this->vatFacade = $vatFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->changeSeoGroup($builder);

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

        $this->setShortDescriptionsUspGroup($builder, $options);

        $builder->get('displayAvailabilityGroup')->get('stockGroup')->remove('stockQuantity');
        $this->stocksGroup($builder);

        $this->formBuilderHelper->disableFieldsByConfigurations($builder, self::DISABLED_FIELDS);
        $this->setPricesGroup($builder, $product);
        $this->buildTransferredFiles($builder, $product);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param \App\Model\Product\Product|null $product
     */
    private function buildTransferredFiles(FormBuilderInterface $builder, ?Product $product): void
    {
        if ($product === null) {
            return;
        }

        $groupBuilder = $builder->create('transferredFilesGroup', GroupType::class, [
            'label' => t('Přenesené soubory'),
        ]);

        $groupBuilder->add('assemblyInstructionFileUrl', MultidomainType::class, [
            'label' => t('Pokyny ke složení'),
            'required' => false,
            'entry_type' => UrlType::class,
        ]);

        $groupBuilder->add('productTypePlanFileUrl', MultidomainType::class, [
            'label' => t('Plán typu produktu'),
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
            ->add('highPriceWithVat', MultidomainType::class, [
                'label' => t('Vyšší cena s DPH'),
                'entry_type' => MoneyType::class,
                'entry_options' => [
                    'scale' => 6,
                ],
                'required' => false,
            ])
            ->add('highPriceWithoutVat', MultidomainType::class, [
                'label' => t('Vyšší cena bez DPH'),
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
                    'label' => t('DPH ' . $domainConfig->getName()),
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
    private function changeSeoGroup(FormBuilderInterface $builder): void
    {
        $builderSeoGroup = $builder->get('seoGroup');

        $builderSeoGroup->remove('seoH1s');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function stocksGroup(FormBuilderInterface $builder)
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
    public function getExtendedType()
    {
        return ProductFormType::class;
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
