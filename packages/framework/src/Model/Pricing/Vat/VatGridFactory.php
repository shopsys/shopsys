<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;

class VatGridFactory implements GridFactoryInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly VatFacade $vatFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create()
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('v, COUNT(rv.id) as asReplacementCount')
            ->from(Vat::class, 'v')
            ->leftJoin(Vat::class, 'rv', Join::WITH, 'v.id = rv.replaceWith')
            ->where('v.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $this->adminDomainTabsFacade->getSelectedDomainId())
            ->groupBy('v');
        $dataSource = new QueryBuilderWithRowManipulatorDataSource($queryBuilder, 'v.id', function ($row) {
            $vat = $this->vatFacade->getById($row['v']['id']);
            $row['vat'] = $vat;

            return $row;
        });

        $grid = $this->gridFactory->create('vatList', $dataSource);
        $grid->setDefaultOrder('name');
        $grid->addColumn('name', 'v.name', t('Name'), true);
        $grid->addColumn('percent', 'v.percent', t('Percent'), true);
        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_vat_deleteconfirm', ['id' => 'v.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/Vat/listGrid.html.twig');

        return $grid;
    }
}
