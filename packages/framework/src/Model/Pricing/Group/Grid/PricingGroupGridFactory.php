<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class PricingGroupGridFactory implements GridFactoryInterface
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
            ->select('pg')
            ->from(PricingGroup::class, 'pg')
            ->where('pg.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $this->adminDomainTabsFacade->getSelectedDomainId());
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'pg.id');

        $grid = $this->gridFactory->create('pricingGroupList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('name');
        $grid->addColumn('name', 'pg.name', t('Name'), true);
        $grid->addDeleteActionColumn('admin_pricinggroup_deleteconfirm', ['id' => 'pg.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAdministration/content/pricing/groups/listGrid.html.twig');

        return $grid;
    }
}
