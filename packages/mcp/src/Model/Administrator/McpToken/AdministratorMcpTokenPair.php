<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

class AdministratorMcpTokenPair
{
    public function __construct(
        public readonly string $publicTokenId,
        public readonly string $secret,
    ) {
    }

    public function getTokenString(): string
    {
        return $this->publicTokenId . '.' . $this->secret;
    }
}
