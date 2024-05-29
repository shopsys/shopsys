<?php

declare(strict_types=1);

namespace Shopsys\Administration\Model\CustomerUser;

use Shopsys\Administration\Component\Admin\AbstractAdmin;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class CustomerUserAdmin extends AbstractAdmin
{
    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');
        $list->add('fullName');
        $list->add('city');
        $list->add('telephone');
        $list->add('email');
        $list->add('isActivated', FieldDescriptionInterface::TYPE_BOOLEAN);
        $list->add('pricingGroup');
        $list->add('ordersCount', null, ['virtual_field' => true]);

        $list->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
                'login_as_user' => [
                    'template' => '@ShopsysAdministration/CustomerUser/list__action_login_as_user.html.twig',
                ],
            ],
        ]);
    }

    /**
     * @param \Sonata\AdminBundle\Route\RouteCollectionInterface $collection
     */
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
        $collection->add('login-as-customer-user', $this->getRouterIdParameter() . '/login-as-user');
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $query
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        /** @var \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery $query */
        $query = parent::configureQuery($query);

        $query->addSelect('o.telephone');
        $query->addSelect('MAX(pg.name) AS pricingGroup');
        $query->addSelect('MAX(ba.city) city');
        $query->addSelect('MAX(CASE WHEN ba.companyCustomer = true
            THEN ba.companyName
                ELSE CONCAT(o.lastName, \' \', o.firstName)
            END) AS name');
        $query->addSelect('COUNT(ord.id) ordersCount');
        $query->addSelect('MAX(ord.createdAt) lastOrderAt');

        $query->join('o.customer', 'c')
            ->leftJoin(BillingAddress::class, 'ba', 'WITH', 'c.id = ba.customer')
            ->leftJoin(Order::class, 'ord', 'WITH', 'ord.customerUser = o.id AND ord.deleted = :deleted')
            ->setParameter('deleted', false)
            ->leftJoin(PricingGroup::class, 'pg', 'WITH', 'pg.id = o.pricingGroup')
            ->setParameter('deleted', false)
            ->groupBy('o.id');

        return $query;
    }
}
