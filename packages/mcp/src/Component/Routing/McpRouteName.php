<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Routing;

class McpRouteName
{
    public const string ADMIN_MCP_OAUTH_AUTHORIZE = 'admin_superadmin_mcp_oauth_authorize';
    public const string ADMIN_MCP_TOKEN = 'admin_superadmin_mcp_token';
    public const string ADMIN_MCP_TOKEN_MANUAL = 'admin_superadmin_mcp_manual_token';
    public const string ADMIN_MCP_TOKEN_REVOKE = 'admin_superadmin_mcp_token_revoke';
    public const string MCP_ENDPOINT = '_mcp_endpoint';
    public const string MCP_OAUTH_METADATA = 'mcp_oauth_metadata';
    public const string MCP_OAUTH_REGISTER = 'mcp_oauth_register';
    public const string MCP_OAUTH_TOKEN = 'mcp_oauth_token';
}
