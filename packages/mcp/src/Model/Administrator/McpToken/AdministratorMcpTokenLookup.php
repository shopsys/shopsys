<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use Psr\Clock\ClockInterface;

class AdministratorMcpTokenLookup
{
    protected const string TOKEN_PATTERN = '/^(?P<publicTokenId>[a-f0-9]{32})\\.(?P<secret>[a-f0-9]{64})$/';

    public function __construct(
        protected readonly AdministratorMcpTokenRepository $administratorMcpTokenRepository,
        protected readonly AdministratorMcpTokenHasher $administratorMcpTokenHasher,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function findValidTokenByTokenString(string $tokenString): ?AdministratorMcpToken
    {
        if (!preg_match(self::TOKEN_PATTERN, $tokenString, $matches)) {
            return null;
        }

        $administratorMcpToken = $this->administratorMcpTokenRepository->findCurrentByPublicTokenId($matches['publicTokenId']);

        if ($administratorMcpToken === null) {
            return null;
        }

        if (!$administratorMcpToken->isValidAt($this->clock->now())) {
            return null;
        }

        if (!$this->administratorMcpTokenHasher->verify($administratorMcpToken->getSecretHash(), $matches['secret'])) {
            return null;
        }

        return $administratorMcpToken;
    }
}
