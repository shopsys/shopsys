<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Security;

class McpBearerToken
{
    public const string HEADER_AUTHORIZATION = 'Authorization';

    protected const string BEARER_PREFIX = 'Bearer ';
    protected const string TOKEN_STRING_PATTERN = '/^(?P<publicTokenId>[a-f0-9]{32})\\.(?P<secret>[a-f0-9]{64})$/';

    public static function hasBearerScheme(string $authorizationHeader): bool
    {
        return strncasecmp($authorizationHeader, self::BEARER_PREFIX, strlen(self::BEARER_PREFIX)) === 0;
    }

    public static function extractTokenString(string $authorizationHeader): string
    {
        return substr($authorizationHeader, strlen(self::BEARER_PREFIX));
    }

    /**
     * @return array{publicTokenId: string, secret: string}|null
     */
    public static function parseTokenString(string $tokenString): ?array
    {
        if (!preg_match(self::TOKEN_STRING_PATTERN, $tokenString, $matches)) {
            return null;
        }

        return [
            'publicTokenId' => $matches['publicTokenId'],
            'secret' => $matches['secret'],
        ];
    }
}
