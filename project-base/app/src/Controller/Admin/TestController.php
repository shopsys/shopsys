<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Order\Order;
use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\Action;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\OrderingEnum;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

#[CrudController(Order::class)]
class TestController extends AbstractCrudController
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfig $config
     * @return \Shopsys\AdministrationBundle\Component\Config\CrudConfig
     */
    protected function configure(CrudConfig $config): CrudConfig
    {
        return $config
            //->setTitle(PageType::LIST, 'Test listing')
            //->setMenuTitle(t('Test menu'))
            //->hideInMenu()
            //->setActions([PageType::LIST, PageType::EDIT])
            //->setRoutePrefix('/SomePrefix/prefix-prefixes/hm/')
            ->setMenuSection('customers', 'promo_codes')
            ->disableAction(ActionType::CREATE)
        ;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig $actions
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionsConfig
     */
    protected function configureActions(ActionsConfig $actions): ActionsConfig
    {
        $actions->add(
            ActionType::LIST,
            Action::create('linkToDashboard', t('Link To dashboard'))
                ->setAttribute('class', 'btn--primary', true)
                ->setOpenInNewTab()
                ->linkToRoute('admin_default_dashboard', fn () => [
                    'id' => 1,
                ]),
        );

        $actions->add(
            ActionType::LIST,
            Action::create('linkToFrontend', t('Link To Frontend'))
                ->linkToUrl(fn () => 'https://www.shopsys.com')
                ->setIcon('forward-page'),
        );

        $actions->add(
            ActionType::LIST,
            Action::create('testLink2', t('Link 2')),
        );
        $actions->update(
            ActionType::LIST,
            'linkToDashboard',
            fn (AbstractAction $actionBuilder) => $actionBuilder->setLabel('New link'),
        );

        $actions->remove(ActionType::LIST, 'testLink2');

        return $actions;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->addSelect('(CASE WHEN o.companyName IS NOT NULL
                    THEN o.companyName
                    ELSE CONCAT(o.lastName, \' \', o.firstName)
                END) AS customerName')
            ->andWhere('o.deleted = :deleted')
            ->setParameter('deleted', false);
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid $datagrid
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Datagrid
     */
    public function configureDatagrid(Datagrid $datagrid): Datagrid
    {
        $datagrid
            ->addIdentifier('id')
            ->add('preview', [
                'label' => t('Preview'),
                'property' => 'id',
                'sortable' => false,
                'template' => 'Admin/Crud/preview.html.twig',
            ])
            ->add('customerName', [
                'label' => t('Customer Name'),
                'virtual' => true,
                'property' => 'customerName',
            ])
            ->add('number', [
                'label' => t('Order Nr.'),
            ])
            ->add('createdAt', [
                'label' => t('Created at'),
            ])
            ->add('domainId', [
                'label' => t('Domain ID'),
            ])
            ->add('currency2', [
                'property' => 'currency',
                'visible' => false,
            ])
            ->add('currency', [
                'label' => t('Currency'),
                'transform' => fn (Currency $currency) => $currency->getCode(),
            ])
            ->add('totalPriceWithVat', [
                'label' => t('Total Price'),
            ])
            ->add('customerUser', [
                'label' => t('Customer'),
                'visible' => false,
            ])
            ->add('customerUser.defaultDeliveryAddress', [
                'label' => t('Billing Address'),
                'visible' => false,
            ])
            ->add('customerUser.firstName', [
                'label' => t('First Name'),
            ])
            ->add('deleted', [
                'label' => t('Deleted'),
            ])
            ->add('customerUser.customer.id', [
                'label' => t('Customer ID'),
            ])
            ->add('transport.id', [
                'label' => t('Transport'),
                'transform' => function (int $value) {
                    return match ($value) {
                        6 => 'Transport 1',
                        5 => 'Transport 2',
                        2 => 'Transport 3',
                        4 => 'Transport 4',
                        default => 'Unknown transport',
                    };
                },
            ])
        ;

        $datagrid->setDefaultOrder('createdAt', OrderingEnum::DESC);

        $datagrid->update('number', [
            'sortable' => false,
        ]);

        $datagrid->actions()->update('edit', [
            'label' => 'Test',
        ]);

        $datagrid->remove('customerUser.firstName');

        $datagrid->reorder(['preview', 'id', 'customerUser.customer.id']);

        return $datagrid;
    }
}
