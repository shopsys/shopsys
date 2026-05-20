<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use DateTimeImmutable;

class AdministratorMcpTokenGenerator
{
    public function generateIssuedToken(DateTimeImmutable $expiresAt): AdministratorMcpIssuedToken
    {
        $publicTokenId = bin2hex(random_bytes(16));
        $secret = bin2hex(random_bytes(32));

        return new AdministratorMcpIssuedToken($publicTokenId, $secret, $expiresAt);
    }
}
