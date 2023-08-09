<?php

declare(strict_types=1);

namespace App\Model\Store\ClosedDay\Grid;

use App\Model\Store\ClosedDay\ClosedDay;
use App\Model\Store\ClosedDay\ClosedDayFacade;
use App\Model\Store\Store;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;

class ClosedDayGridFactory
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \App\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly GridFactory $gridFactory,
        private readonly ClosedDayFacade $closedDayFacade,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(int $domainId): Grid
    {
        $queryBuilder = $this->em
            ->createQueryBuilder()
            ->select('cd')
            ->from(ClosedDay::class, 'cd')
            ->where('cd.domainId = :domainId')
            ->groupBy('cd')
            ->setParameter('domainId', $domainId);

        $grid = $this->gridFactory->create('closedDayList', new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'cd.id',
            function (array $row): array {
                $closedDay = $this->closedDayFacade->getById($row['cd']['id']);

                $row['cd']['excludedStores'] = array_map(fn (Store $store): array => [
                    'id' => $store->getId(),
                    'name' => $store->getName(),
                ], $closedDay->getExcludedStores());

                return $row;
            },
        ));
        $grid->enablePaging();
        $grid->setDefaultOrder('date');
        $grid->addColumn('name', 'cd.name', t('Name'), true);
        $grid->addColumn('date', 'cd.date', t('Date'), true);
        $grid->addColumn('excludedStores', 'cd.excludedStores', t('Excluded stores'));
        $grid->addEditActionColumn('admin_closedday_edit', ['id' => 'cd.id']);
        $grid->addDeleteActionColumn('admin_closedday_delete', ['id' => 'cd.id'])->setConfirmMessage(
            t('Do you really want to remove this holiday / internal day?'),
        );
        $grid->setTheme('/Admin/Content/ClosedDay/listGrid.html.twig', [
            'domainId' => $domainId,
        ]);

        return $grid;
    }
}
