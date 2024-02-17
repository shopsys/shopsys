<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Admin\Products;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\AdminBundle\Component\Admin\AbstractAdmin;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ProductParameterValueFormType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueProductParameters;
use Shopsys\FrameworkBundle\Form\LocalizedFullWidthType;
use Shopsys\FrameworkBundle\Form\Transformers\ProductParameterValueToProductParameterValuesLocalizedTransformer;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;

class ProductAdmin extends AbstractAdmin
{

    public function __construct(
        private readonly ProductParameterValueToProductParameterValuesLocalizedTransformer $productParameterValueToProductParameterValuesLocalizedTransformer,
    )
    {
        parent::__construct();
    }

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
            ]
        ]);
    }

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


    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('catnum');
        $filter->add('sellingDenied');
    }


    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
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