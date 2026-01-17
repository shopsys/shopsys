<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model\Grid;

use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter\ResolvedChangesFormatter;
use Shopsys\FrameworkBundle\Component\EntityLog\Enum\EntityLogActionEnum;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogRepository;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;

class EntityLogGridFactory
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly EntityLogRepository $entityLogRepository,
        protected readonly ResolvedChangesFormatter $resolvedChangesFormatter,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
    ) {
    }

    public function createByEntityNameAndEntityId(string $entityName, int $entityId): Grid
    {
        $queryBuilder = $this->entityLogRepository->getQueryBuilderByEntityNameAndEntityId($entityName, $entityId);
        $queryBuilder->andWhere('(el.action = :createAction AND el.parentEntityId IS NULL) OR el.action != :createAction');
        $queryBuilder->setParameter('createAction', EntityLogActionEnum::CREATE);

        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
            $queryBuilder,
            'el.id',
            function ($row) {
                $row['el']['changeSet'] = $this->resolvedChangesFormatter->formatResolvedChanges($row['el']['changeSet']);

                return $row;
            },
        );

        $grid = $this->gridFactory->create('entityLogList', $dataSource, SystemRole::ADMIN);

        $grid->addColumn('userIdentifier', 'el.userIdentifier', t('User'));
        $grid->addColumn('action', 'el.action', t('Action'));
        $grid->addColumn('entityName', 'el.entityName', t('Entity'));
        $grid->addColumn('entityIdentifier', 'el.entityIdentifier', t('Entity identifier'));
        $grid->addColumn('changeSet', 'el.changeSet', t('Changes'));
        $grid->addColumn('createAt', 'el.createdAt', t('Date'));

        $grid->setTitle(t('Entity log'));
        $grid->setTheme('@ShopsysAdministration/content/entityLog/listGrid.html.twig');

        return $grid;
    }
}
