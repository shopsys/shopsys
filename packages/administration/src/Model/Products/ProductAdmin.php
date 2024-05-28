<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Products;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\Administration\Component\Admin\AbstractAdmin;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ProductParameterValueFormType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueProductParameters;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\Transformers\ProductParameterValueToProductParameterValuesLocalizedTransformer;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Image;

class ProductAdmin extends AbstractAdmin
{
    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\ProductParameterValueToProductParameterValuesLocalizedTransformer $productParameterValueToProductParameterValuesLocalizedTransformer
     */
    public function __construct(
        protected readonly ProductParameterValueToProductParameterValuesLocalizedTransformer $productParameterValueToProductParameterValuesLocalizedTransformer,
    ) {
        parent::__construct();
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->add('name');
        $list->add('catnum');
        $list->add('sellingDenied');

        $list->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
                'edit' => [],
                'delete' => [],
            ],
        ]);
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product|null $product */
        $product = $this->getSubject();
        $form->with('Basic information')
                ->add('name', LocalizedFullWidthType::class)
                ->add('catnum', TextType::class, [
                    'attr' => [
                        'icon' => true,
                        'iconTitle' => t('Whatever man'),
                    ],
                ])
                ->add('ean')
            ->add('orderingPriorityByDomainId', MultidomainType::class, [
                'entry_type' => PercentType::class,
                'entry_options' => [
                    'required' => true,
                ],
                'label' => t('Sorting priority'),
            ])
                ->add('sellingDenied', YesNoType::class)
            ->end()
            ->with('Parameters')
                ->add('parameters', CollectionType::class, [
                    'entry_type' => ProductParameterValueFormType::class,
                    'constraints' => [
                        new UniqueProductParameters([
                            'message' => 'Parameter {{ parameterName }} is used more than once',
                        ]),
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                ])
            ->end()
            ->with('images')
            ->add('images', ImageUploadType::class, [
                'required' => false,
                'image_entity_class' => Product::class,
                'file_constraints' => [
                    new Image([
                        'mimeTypes' => ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'],
                        'mimeTypesMessage' => 'Image can be only in JPG, GIF or PNG format',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Uploaded image is to large ({{ size }} {{ suffix }}). '
                            . 'Maximum size of an image is {{ limit }} {{ suffix }}.',
                    ]),
                ],
                'entity' => $product,
                'info_text' => t('You can upload following formats: PNG, JPG, GIF'),
                'label' => t('Images'),
            ])
            ->end()
            ->with('accessories')
            ->add('accessories', ProductsType::class, [
                'required' => false,
                'main_product' => $product,
                'sortable' => true,
                'label' => t('Accessories'),
            ])
            ->end();

        $form
            ->get('parameters')
            ->addModelTransformer($this->productParameterValueToProductParameterValuesLocalizedTransformer);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('catnum');
        $filter->add('sellingDenied');
    }

    /**
     * @param \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery $query
     * @return \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery
     */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        /** @var \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery $query */
        $query = parent::configureQuery($query);
        $query
            ->addSelect('pmip.inputPrice AS priceForProductList')
            ->leftJoin(
                ProductManualInputPrice::class,
                'pmip',
                Join::WITH,
                'pmip.product = o.id AND pmip.pricingGroup = :pricingGroupId',
            )
            ->setParameters([
                'pricingGroupId' => 1,
            ]);

        return $query;
    }
}
