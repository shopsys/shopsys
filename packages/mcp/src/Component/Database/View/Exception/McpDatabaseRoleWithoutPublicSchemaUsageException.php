<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\View\Exception;

use RuntimeException;

class McpDatabaseRoleWithoutPublicSchemaUsageException extends RuntimeException
{
    public function __construct(string $mcpDatabaseUser)
    {
        parent::__construct(sprintf(
            'MCP database role "%s" (MCP_DATABASE_USER) has no USAGE privilege on the public schema, so MCP queries on tables with expression indexes would fail. Grant it with GRANT USAGE ON SCHEMA public TO "%s", or leave MCP_DATABASE_USER and MCP_DATABASE_PASSWORD empty to disable MCP.',
            $mcpDatabaseUser,
            $mcpDatabaseUser,
        ));
    }
}
