<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\OAuth;

class McpOAuthProtocol
{
    public const string RESPONSE_TYPE_CODE = 'code';
    public const string GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    public const string TOKEN_ENDPOINT_AUTH_METHOD_NONE = 'none';
    public const string CODE_CHALLENGE_METHOD_S256 = 'S256';
    public const string TOKEN_TYPE_BEARER = 'Bearer';
}
