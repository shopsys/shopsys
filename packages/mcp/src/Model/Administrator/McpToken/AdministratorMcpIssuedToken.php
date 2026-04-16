<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use DateTimeImmutable;

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
        return $this->publicTokenId . '.' . $this->secret;
    }
}
