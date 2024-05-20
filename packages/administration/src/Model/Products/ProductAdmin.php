<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\Products;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\Administration\Component\Admin\AbstractAdmin;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ProductParameterValueFormType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueProductParameters;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Shopsys\FrameworkBundle\Form\Transformers\ProductParameterValueToProductParameterValuesLocalizedTransformer;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;

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
        $list->add(
            'name',
            null,
            [
                'label_icon' => 'fas fa-thumbs-up',
                'help' => 'This is a name of the product... duh!',
            ],
        );
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
        $form->with('Basic information')
                ->add('name', LocalizedFullWidthType::class)
                ->add('catnum')
                ->add('ean')
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
