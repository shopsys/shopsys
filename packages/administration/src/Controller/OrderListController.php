<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\AdminOrderStatusFilterFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class OrderListController extends AdminBaseController
{
    protected const string ORDERS_LIST_FOR_GRID_CACHE_KEY = 'ORDERS_LIST_FOR_GRID_CACHE_KEY';

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderAdvancedSearchFacade $orderAdvancedSearchFacade,
        protected readonly Domain $domain,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly AdminOrderStatusFilterFacade $adminOrderStatusFilterFacade,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly GridFactory $gridFactory,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    #[Route(path: '/order/preview/{id}', requirements: ['id' => '\d+'], name: 'admin_order_preview')]
    #[CanView]
    public function previewAction(int $id): Response
    {
        $order = $this->orderFacade->getById($id);

        return $this->render('@ShopsysAdministration/content/order/preview.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route(path: '/order/delete/{id}', requirements: ['id' => '\d+'], name: 'admin_order_delete')]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $orderNumber = $this->orderFacade->getById($id)->getNumber();

            $this->orderFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Order Nr. <strong>{{ number }}</strong> deleted'),
                [
                    'number' => $orderNumber,
                ],
            );
        } catch (OrderNotFoundException) {
            $this->addErrorFlash(t('Selected order doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_order_list');
    }

    #[Route(path: '/order/list/', name: 'admin_order_list')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $domainFilterNamespace = 'orders';

        $advancedSearchForm = $this->orderAdvancedSearchFacade->createAdvancedSearchForm($request);
        $advancedSearchData = $advancedSearchForm->getData();

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $isAdvancedSearchFormSubmitted = $this->orderAdvancedSearchFacade->isAdvancedSearchFormSubmitted(
            $request,
        );

        if ($isAdvancedSearchFormSubmitted) {
            $queryBuilder = $this->orderAdvancedSearchFacade->getQueryBuilderByAdvancedSearchOrderData(
                $advancedSearchData,
            );
        } else {
            $queryBuilder = $this->orderFacade->getOrderListQueryBuilderByQuickSearchData($quickSearchForm->getData());
        }

        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

        if ($selectedDomainId !== null) {
            $queryBuilder
                ->andWhere('o.domainId = :selectedDomainId')
                ->setParameter('selectedDomainId', $selectedDomainId);
        } else {
            $queryBuilder
                ->andWhere('o.domainId IN (:domainIds)')
                ->setParameter('domainIds', $this->domain->getAdminEnabledDomainIds());
        }

        $selectedOrderStatusId = $this->adminOrderStatusFilterFacade->getSelectedOrderStatusId();

        if ($selectedOrderStatusId !== null) {
            $queryBuilder
                ->andWhere('o.status = :selectedOrderStatusId')
                ->setParameter('selectedOrderStatusId', $selectedOrderStatusId);
        }

        $grid = $this->getOrdersGrid($queryBuilder);

        return $this->render('@ShopsysAdministration/content/order/list.html.twig', [
            'gridView' => $grid->createView(),
            'domainFilterNamespace' => $domainFilterNamespace,
            'quickSearchForm' => $quickSearchForm->createView(),
            'advancedSearchForm' => $advancedSearchForm->createView(),
            'isAdvancedSearchFormSubmitted' => $this->orderAdvancedSearchFacade->isAdvancedSearchFormSubmitted(
                $request,
            ),
        ]);
    }

    protected function getOrdersGrid(QueryBuilder $queryBuilder): Grid
    {
        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
            $queryBuilder,
            'o.id',
            function ($row, $rows) {
                return $this->addOrderEntityToDataSource($row, array_column($rows, 'id'));
            },
        );

        $grid = $this->gridFactory->create('orderList', $dataSource, AdminRoleConstant::ROLE_ORDER);
        $grid->enablePaging();
        $grid->setDefaultOrder('created_at', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('number', 'o.number', t('Order Nr.'), true);
        $grid->addColumn('created_at', 'o.createdAt', t('Created'), true);
        $grid->addColumn('customer_name', 'customerName', t('Customer'), true);
        $grid->addColumn('status_name', 'statusName', t('Status'), true);
        $grid->addColumn('total_price', 'o.totalPriceWithVat', t('Total price'))
            ->setClassAttribute('text-end text-nowrap');

        if ($this->domain->isMultidomain()) {
            $grid->addColumn('domain_id', 'o.domainId', t('Domain'), true)->setClassAttribute('w-1 d-none d-md-table-cell text-center');
        }


        $grid->addEditActionColumn('admin_order_edit', ['id' => 'id']);
        $grid->addDeleteActionColumn('admin_order_delete', ['id' => 'id'])
            ->setConfirmMessage(t('Do you really want to remove the order?'));

        $grid->setActionColumnClassAttribute('text-center');
        $grid->setTheme('@ShopsysAdministration/content/order/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        return $grid;
    }

    /**
     * @param int[] $ids
     */
    protected function addOrderEntityToDataSource(array $row, array $ids): array
    {
        $ordersIndexedById = $this->inMemoryCache->getOrSaveValue(
            self::ORDERS_LIST_FOR_GRID_CACHE_KEY,
            fn () => $this->orderFacade->findByIds($ids),
            self::ORDERS_LIST_FOR_GRID_CACHE_KEY,
            ...$ids,
        );

        $row['order'] = $ordersIndexedById[$row['id']];

        return $row;
    }
}
