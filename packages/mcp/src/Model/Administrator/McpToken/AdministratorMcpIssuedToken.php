<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\McpBundle\Component\Security\McpBearerToken;

class AdministratorMcpIssuedToken
{
    public function __construct(
        public readonly string $publicTokenId,
        public readonly string $secret,
        public readonly DateTimeImmutable $expiresAt,
    ) {
    }

    public function getTokenString(): string
    {
        return ExtendedClassNameResolver::resolve(McpBearerToken::class)::createTokenString($this->publicTokenId, $this->secret);
    }
}
