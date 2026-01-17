<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;

class VatGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly VatFacade $vatFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('v, COUNT(rv.id) as asReplacementCount')
            ->from(Vat::class, 'v')
            ->leftJoin(Vat::class, 'rv', Join::WITH, 'v.id = rv.replaceWith')
            ->where('v.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $this->adminDomainTabsFacade->getSelectedDomainId())
            ->groupBy('v');
        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create($queryBuilder, 'v.id', function ($row) {
            $vat = $this->vatFacade->getById($row['v']['id']);
            $row['vat'] = $vat;

            return $row;
        });

        $grid = $this->gridFactory->create('vatList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('name');
        $grid->addColumn('name', 'v.name', t('Name'), true);
        $grid->addColumn('percent', 'v.percent', t('Percent'), true);
        $grid->addDeleteActionColumn('admin_vat_deleteconfirm', ['id' => 'v.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAdministration/content/vat/listGrid.html.twig');

        return $grid;
    }
}
