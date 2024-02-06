<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model\Grid;

use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ResolvedChangesFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;

class EntityLogGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository $entityLogRepository
     * @param \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ResolvedChangesFormatter $resolvedChangesFormatter
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly EntityLogRepository $entityLogRepository,
        protected readonly ResolvedChangesFormatter $resolvedChangesFormatter,
    ) {
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function createByEntityNameAndEntityId(string $entityName, int $entityId): Grid
    {
        $queryBuilder = $this->entityLogRepository->getQueryBuilderByEntityNameAndEntityId($entityName, $entityId);
        $queryBuilder->andWhere('(el.action = :createAction AND el.parentEntityId IS NULL) OR el.action != :createAction');
        $queryBuilder->setParameter('createAction', EntityLogActionEnum::CREATE);

        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'el.id',
            function ($row) {
                $row['el']['changeSet'] = $this->resolvedChangesFormatter->formatResolvedChanges($row['el']['changeSet']);

                return $row;
            },
        );

        $grid = $this->gridFactory->create('entityLogList', $dataSource);

        $grid->addColumn('userIdentifier', 'el.userIdentifier', t('User'));
        $grid->addColumn('action', 'el.action', t('Action'));
        $grid->addColumn('entityName', 'el.entityName', t('Entity'));
        $grid->addColumn('entityIdentifier', 'el.entityIdentifier', t('Entity identifier'));
        $grid->addColumn('changeSet', 'el.changeSet', t('Changes'));
        $grid->addColumn('createAt', 'el.createdAt', t('Date'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/EntityLog/listGrid.html.twig');

        return $grid;
    }
}
