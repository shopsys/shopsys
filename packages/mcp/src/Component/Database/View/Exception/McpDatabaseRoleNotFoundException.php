<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\View\Exception;

use RuntimeException;

class McpDatabaseRoleNotFoundException extends RuntimeException
{
    public function __construct(string $mcpDatabaseUser)
    {
        parent::__construct(sprintf(
            'MCP database role "%s" (MCP_DATABASE_USER) does not exist, so the MCP database views cannot be granted to it. Create the role, or leave MCP_DATABASE_USER and MCP_DATABASE_PASSWORD empty to disable MCP.',
            $mcpDatabaseUser,
        ));
    }
}
