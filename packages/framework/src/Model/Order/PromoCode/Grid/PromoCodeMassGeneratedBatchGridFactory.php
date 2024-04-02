<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class PromoCodeMassGeneratedBatchGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('DISTINCT pc.massGenerateBatchId AS batchId, pc.prefix')
            ->from(PromoCode::class, 'pc')
            ->andWhere('pc.massGenerateBatchId IS NOT NULL')
            ->orderBy('batchId', 'DESC');

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pc.id');

        $grid = $this->gridFactory->create('promoCodeList', $dataSource);
        $grid->setDefaultOrder('batchId');
        $grid->addColumn('code', 'batchId', t('Batch ID'), true);
        $grid->addColumn('prefix', 'pc.prefix', t('Promo code prefix'), true);
        $grid->setActionColumnClassAttribute('table-col table-col-10');

        $grid->addActionColumn('download', t('Download file'), 'admin_promocode_downloadmassgeneratebatch', ['batchId' => 'batchId']);

        return $grid;
    }
}
