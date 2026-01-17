<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class PromoCodeMassGeneratedBatchGridFactory
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly EntityManagerInterface $em,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    public function create(): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('DISTINCT pc.massGenerateBatchId AS batchId, pc.prefix')
            ->from(PromoCode::class, 'pc')
            ->andWhere('pc.massGenerateBatchId IS NOT NULL')
            ->orderBy('batchId', 'DESC');

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'pc.id');

        $grid = $this->gridFactory->create('promoCodeList', $dataSource, AdminRoleConstant::ROLE_PROMO_CODE);
        $grid->setDefaultOrder('batchId');
        $grid->addColumn('code', 'batchId', t('Batch ID'), true);
        $grid->addColumn('prefix', 'pc.prefix', t('Promo code prefix'), true);

        $grid->addActionColumn('download', t('Download file'), 'admin_promocode_downloadmassgeneratebatch', ['batchId' => 'batchId']);

        return $grid;
    }
}
