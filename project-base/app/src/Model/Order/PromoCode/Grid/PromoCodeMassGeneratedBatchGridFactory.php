<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\Grid;

use App\Model\Order\PromoCode\PromoCode;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;

class PromoCodeMassGeneratedBatchGridFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        GridFactory $gridFactory,
        EntityManagerInterface $em
    ) {
        $this->gridFactory = $gridFactory;
        $this->em = $em;
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
        $grid->addColumn('code', 'batchId', t('Dávka ID'), true);
        $grid->addColumn('prefix', 'pc.prefix', t('Prefix kuponu'), true);
        $grid->setActionColumnClassAttribute('table-col table-col-10');

        $grid->addActionColumn('download', t('Stáhnout soubor'), 'admin_promocode_downloadmassgeneratebatch', ['batchId' => 'batchId']);

        return $grid;
    }
}
