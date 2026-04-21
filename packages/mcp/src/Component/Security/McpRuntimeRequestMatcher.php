<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Security;

use Symfony\Component\HttpFoundation\Request;

class McpRuntimeRequestMatcher
{
    public const string PATH_PREFIX = '/_mcp';

    public static function isMcpRuntimeRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), self::PATH_PREFIX);
    }
}
