<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Routing;

class McpRouteName
{
    public const string ADMIN_MCP_ROUTE_NAME_PREFIX = 'admin_superadmin_mcp_';
    public const string ADMIN_MCP_OAUTH_AUTHORIZE = self::ADMIN_MCP_ROUTE_NAME_PREFIX . 'oauth_authorize';
    public const string ADMIN_MCP_TOKEN = self::ADMIN_MCP_ROUTE_NAME_PREFIX . 'token';
    public const string ADMIN_MCP_TOKEN_MANUAL = self::ADMIN_MCP_ROUTE_NAME_PREFIX . 'manual_token';
    public const string ADMIN_MCP_TOKEN_REVOKE = self::ADMIN_MCP_ROUTE_NAME_PREFIX . 'token_revoke';
    public const string MCP_ENDPOINT = '_mcp_endpoint';
    public const string MCP_OAUTH_METADATA = 'mcp_oauth_metadata';
    public const string MCP_OAUTH_REGISTER = 'mcp_oauth_register';
    public const string MCP_OAUTH_TOKEN = 'mcp_oauth_token';
}
