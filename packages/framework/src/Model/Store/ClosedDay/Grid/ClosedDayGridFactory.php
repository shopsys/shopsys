<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Store\Store;

class ClosedDayGridFactory
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
    ) {
    }

    public function create(int $domainId): Grid
    {
        $queryBuilder = $this->em
            ->createQueryBuilder()
            ->select('cd')
            ->from(ClosedDay::class, 'cd')
            ->where('cd.domainId = :domainId')
            ->groupBy('cd')
            ->setParameter('domainId', $domainId);

        $grid = $this->gridFactory->create(
            'closedDayList',
            $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
                $queryBuilder,
                'cd.id',
                function (array $row): array {
                    $closedDay = $this->closedDayFacade->getById($row['cd']['id']);

                    $row['cd']['excludedStores'] = array_map(static fn (Store $store): array => [
                        'id' => $store->getId(),
                        'name' => $store->getName(),
                    ], $closedDay->getExcludedStores());

                    return $row;
                },
            ),
            AdminRoleConstant::ROLE_CLOSED_DAYS,
        );
        $grid->enablePaging();
        $grid->setDefaultOrder('date');
        $grid->addColumn('name', 'cd.name', t('Name'), true);
        $grid->addColumn('date', 'cd.date', t('Date'), true);
        $grid->addColumn('publicHoliday', 'cd.isPublicHoliday', t('Public holiday'), true);
        $grid->addColumn('excludedStores', 'cd.excludedStores', t('Excluded stores'));
        $grid->addEditActionColumn('admin_closedday_edit', ['id' => 'cd.id']);
        $grid->addDeleteActionColumn('admin_closedday_delete', ['id' => 'cd.id'])
            ->setConfirmMessage(t('Do you really want to remove this holiday / internal day?'));
        $grid->setTheme('@ShopsysAdministration/content/closedDay/listGrid.html.twig', [
            'domainId' => $domainId,
        ]);

        return $grid;
    }
}
