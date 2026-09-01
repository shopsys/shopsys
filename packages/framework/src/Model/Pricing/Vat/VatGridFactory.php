<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;

class VatGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('v')
            ->from(Vat::class, 'v')
            ->where('v.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $this->adminDomainTabsFacade->getSelectedDomainId());
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'v.id');

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
