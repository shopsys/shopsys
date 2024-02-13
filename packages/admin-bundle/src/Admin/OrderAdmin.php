<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Admin;

use Shopsys\AdminBundle\Component\Admin\AbstractAdmin;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\NumberFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OrderAdmin extends AbstractAdmin
{
    public function __construct(
        private readonly Domain $domain,
    )
    {
        parent::__construct();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('firstName');
        $form->add('lastName');
        $form->add('email');
        $form->add('telephone');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('domainId', ChoiceFilter::class, [
            'label' => 'Domain',
            'show_filter' => true,
            'field_type' => ChoiceType::class,
            'field_options' => [
                'choices' => array_flip($this->domain->getAllDomainsAsChoices()),
            ],
        ]);
        $filter->add('number');
        $filter->add('totalPriceWithVat', NumberFilter::class);
        $filter->add('status', null, [
            'field_type' => EntityType::class,
            'field_options' => [
                'class' => 'Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus',
                'choice_label' => 'name',
            ],
        ]);
    }


    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->remove('id');
        $list->add('number');
        $list->add('createdAt');
        $list->add('customerName');
        $list->add('domainId', 'domain', [
            'label' => 'Domain',
        ]);
        $list->add('status.name', 'status', [
            'label' => 'Status',
        ]);
        $list->add('totalPriceWithVat', null, [
            'label' => 'Total price',
            'template' => '@ShopsysAdmin/templates/orderPrice.html.twig',
        ]);

        $list->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
                'edit' => [],
                'delete' => [],
            ]
        ]);
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);
        $query->addSelect('(CASE WHEN o.companyName IS NOT NULL
                    THEN o.companyName
                    ELSE CONCAT(o.lastName, \' \', o.firstName)
                END) AS customerName')
            ->andWhere('o.deleted = FALSE');

        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
    }
}