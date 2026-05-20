<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Security;

use Shopsys\McpBundle\Component\Routing\McpRouteName;
use Symfony\Component\HttpFoundation\Request;

class McpRequestMatcher
{
    public static function isMcpRequest(Request $request): bool
    {
        return self::isMcpRuntimeRequest($request)
            || self::isMcpOauthRequest($request)
            || self::isMcpAdminRequest($request);
    }

    public static function isMcpRuntimeRequest(Request $request): bool
    {
        return self::getRouteNameFromRequest($request) === McpRouteName::MCP_ENDPOINT;
    }

    public static function isMcpOauthRequest(Request $request): bool
    {
        return in_array(self::getRouteNameFromRequest($request), [
            McpRouteName::MCP_OAUTH_METADATA,
            McpRouteName::MCP_OAUTH_REGISTER,
            McpRouteName::MCP_OAUTH_TOKEN,
            McpRouteName::ADMIN_MCP_OAUTH_AUTHORIZE,
        ], true);
    }

    public static function isMcpAdminRequest(Request $request): bool
    {
        $routeName = self::getRouteNameFromRequest($request);

        return $routeName !== McpRouteName::ADMIN_MCP_OAUTH_AUTHORIZE
            && str_starts_with($routeName, McpRouteName::ADMIN_MCP_ROUTE_NAME_PREFIX);
    }

    protected static function getRouteNameFromRequest(Request $request): string
    {
        return $request->attributes->getString('_route');
    }
}
