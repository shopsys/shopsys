<?php

declare(strict_types=1);

namespace App\Model\Order\Status\Grid;

use App\Model\Order\Status\OrderStatus;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Order\Status\Grid\OrderStatusGridFactory as BaseGridOrderStatusGridFactory;

class OrderStatusGridFactory extends BaseGridOrderStatusGridFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): \Shopsys\FrameworkBundle\Component\Grid\Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
           ->select('os, ost')
           ->from(OrderStatus::class, 'os')
           ->join('os.translations', 'ost', Join::WITH, 'ost.locale = :locale')
           ->setParameter('locale', $this->localization->getAdminLocale());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'os.id');

        $grid = $this->gridFactory->create('orderStatusList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'ost.name', t('Name'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_orderstatus_deleteconfirm', ['id' => 'os.id'])
           ->setAjaxConfirm();

        $grid->setTheme('Admin/Content/OrderStatus/listGrid.html.twig', [
            'TYPE_NEW' => OrderStatus::TYPE_NEW,
            'TYPE_DONE' => OrderStatus::TYPE_DONE,
            'TYPE_CANCELED' => OrderStatus::TYPE_CANCELED,
        ]);

        return $grid;
    }
}
