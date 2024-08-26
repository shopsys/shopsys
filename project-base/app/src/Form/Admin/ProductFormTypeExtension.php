<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Form\Constraints\UniqueProductCatnum;
use App\Model\Product\Product;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints;

class ProductFormTypeExtension extends AbstractTypeExtension
{
    public const DISABLED_FIELDS = [];

    /**
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        private readonly FormBuilderHelper $formBuilderHelper,
        private readonly UrlGeneratorInterface $urlGenerator,
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
        $this->setDisplayAvailabilityGroup($builder, $product);
        $this->setPricesGroup($builder, $product);
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

        $groupBuilder->get('stockGroup')
            ->remove('stockQuantity')
            ->remove('outOfStockAction')
            ->remove('outOfStockAvailability');

        $groupBuilder
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
            ->add('usingStock', YesNoType::class, [
                'data' => true,
                'required' => false,
                'disabled' => true,
                'label' => t('Use stocks'),
            ]);
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
    private function setSeoGroup(FormBuilderInterface $builder): void
    {
        $builderSeoGroup = $builder->get('seoGroup');

        $builderSeoGroup->remove('seoH1s');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function setVideoGroup(FormBuilderInterface $builder)
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
