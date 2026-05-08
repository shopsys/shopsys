<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken\Grid;

use Shopsys\FrameworkBundle\Component\Grid\ActionColumn;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenRepository;

class McpTokenGridFactory
{
    public function __construct(
        protected readonly AdministratorMcpTokenRepository $administratorMcpTokenRepository,
        protected readonly GridFactory $gridFactory,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    public function create(Administrator $administrator): Grid
    {
        $queryBuilder = $this->administratorMcpTokenRepository->createTokensByAdministratorQueryBuilder($administrator);
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'amt.id');

        $grid = $this->gridFactory->create('mcpTokenList', $dataSource, SystemRole::ADMIN);
        $grid->setDefaultOrder('createdAt', DataSourceInterface::ORDER_DESC);
        $grid->setTitle(t('MCP tokens'));

        $grid->addColumn('status', 'amt.id', t('Status'));
        $grid->addColumn('type', 'amt.type', t('Type'), true);
        $grid->addColumn('label', 'amt.label', t('Label'), true);
        $grid->addColumn('createdAt', 'amt.createdAt', t('Created at'), true);
        $grid->addColumn('lastUsedAt', 'amt.lastUsedAt', t('Last used at'), true);
        $grid->addColumn('expiresAt', 'amt.expiresAt', t('Expires at'), true);

        $grid->addActionColumn(
            ActionColumn::TYPE_DELETE,
            t('Revoke access'),
            'admin_superadmin_mcp_token_revoke',
            ['id' => 'amt.id'],
        )->setConfirmMessage(t('Do you really want to revoke this MCP token?'));

        $grid->setTheme('@ShopsysMcp/content/superadmin/listGrid.html.twig');

        return $grid;
    }
}
