<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

class AdministratorMcpTokenGenerator
{
    public function generateTokenPair(): AdministratorMcpTokenPair
    {
        $publicTokenId = bin2hex(random_bytes(16));
        $secret = bin2hex(random_bytes(32));

        return new AdministratorMcpTokenPair($publicTokenId, $secret);
    }
}
