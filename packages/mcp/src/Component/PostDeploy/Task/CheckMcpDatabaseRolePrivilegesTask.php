<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\PostDeploy\Task;

use Override;
use Shopsys\FrameworkBundle\Component\PostDeploy\Task\PostDeployTaskInterface;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Database\User\McpDatabaseRoleRepository;
use Shopsys\McpBundle\Component\PostDeploy\Task\Exception\McpDatabaseRoleCanReadPublicSchemaTablesException;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The views in the MCP schema keep hidden columns away from MCP queries only when the MCP database role has no privileges
 * on the tables in the public schema. On staging and production servers the privileges of the role are reduced manually
 * by the hosting (see the upgrade notes), so the task fails the deploy until it is done, instead of leaving the SQL validator
 * as the only barrier without anyone noticing.
 */
class CheckMcpDatabaseRolePrivilegesTask implements PostDeployTaskInterface
{
    public const string POST_DEPLOY_TASK_NAME = 'check_mcp_database_role_privileges';

    public function __construct(
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
        protected readonly McpDatabaseRoleRepository $mcpDatabaseRoleRepository,
        protected readonly string $databaseUser,
        protected readonly string $mcpDatabaseUser,
    ) {
    }

    #[Override]
    public function run(SymfonyStyle $style): void
    {
        if (!$this->mcpAvailabilityChecker->isAvailable()) {
            $style->info('MCP database credentials are not configured, the MCP database role was not checked.');

            return;
        }

        if ($this->databaseUser === $this->mcpDatabaseUser) {
            $style->warning(sprintf(
                'MCP_DATABASE_USER is the application database user "%s", so only the search_path and the SQL validator keep MCP queries away from the hidden columns, the database does not. Use a dedicated MCP database role when the database holds production data.',
                $this->mcpDatabaseUser,
            ));

            return;
        }

        $readableTableNames = $this->mcpDatabaseRoleRepository->getPublicSchemaTableNamesReadableByRole($this->mcpDatabaseUser);
        $hasDefaultPrivileges = $this->mcpDatabaseRoleRepository->hasDefaultPrivilegesOnPublicSchemaTables($this->mcpDatabaseUser);

        if ($readableTableNames !== [] || $hasDefaultPrivileges) {
            throw new McpDatabaseRoleCanReadPublicSchemaTablesException(
                $this->mcpDatabaseUser,
                $this->databaseUser,
                $readableTableNames,
                $hasDefaultPrivileges,
            );
        }

        $style->success(sprintf(
            'MCP database role "%s" has no privileges on the tables in the public schema.',
            $this->mcpDatabaseUser,
        ));
    }
}
