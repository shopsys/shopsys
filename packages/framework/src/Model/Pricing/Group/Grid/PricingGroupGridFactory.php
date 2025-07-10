<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Group\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class PricingGroupGridFactory implements GridFactoryInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @param string|null $editRole
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    #[Override]
    public function create(?string $editRole): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('pg')
            ->from(PricingGroup::class, 'pg')
            ->where('pg.domainId = :selectedDomainId')
            ->setParameter('selectedDomainId', $this->adminDomainTabsFacade->getSelectedDomainId());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pg.id');

        $grid = $this->gridFactory->create('pricingGroupList', $dataSource, $editRole);
        $grid->setDefaultOrder('name');
        $grid->addColumn('name', 'pg.name', t('Name'), true);
        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_pricinggroup_deleteconfirm', ['id' => 'pg.id'])
            ?->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/Pricing/Groups/listGrid.html.twig');

        return $grid;
    }
}
