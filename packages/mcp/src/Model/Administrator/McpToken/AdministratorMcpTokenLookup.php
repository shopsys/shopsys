<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use Psr\Clock\ClockInterface;
use Shopsys\McpBundle\Component\Security\McpBearerToken;

class AdministratorMcpTokenLookup
{
    public function __construct(
        protected readonly AdministratorMcpTokenRepository $administratorMcpTokenRepository,
        protected readonly AdministratorMcpTokenHasher $administratorMcpTokenHasher,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function findValidTokenByTokenString(string $tokenString): ?AdministratorMcpToken
    {
        $tokenParts = McpBearerToken::parseTokenString($tokenString);

        if ($tokenParts === null) {
            return null;
        }

        $administratorMcpToken = $this->administratorMcpTokenRepository->findCurrentByPublicTokenId($tokenParts['publicTokenId']);

        if ($administratorMcpToken === null) {
            return null;
        }

        if (!$administratorMcpToken->isValidAt($this->clock->now())) {
            return null;
        }

        if (!$this->administratorMcpTokenHasher->verify($administratorMcpToken->getSecretHash(), $tokenParts['secret'])) {
            return null;
        }

        return $administratorMcpToken;
    }
}
