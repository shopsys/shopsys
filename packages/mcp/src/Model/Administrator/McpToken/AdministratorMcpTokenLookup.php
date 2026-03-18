<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

class AdministratorMcpTokenLookup
{
    public function __construct(
        protected readonly AdministratorMcpTokenRepository $administratorMcpTokenRepository,
        protected readonly AdministratorMcpTokenHasher $administratorMcpTokenHasher,
    ) {
    }

    public function findValidTokenByTokenString(string $tokenString): ?AdministratorMcpToken
    {
        $tokenParts = explode('.', $tokenString, 2);

        if (count($tokenParts) !== 2 || $tokenParts[0] === '' || $tokenParts[1] === '') {
            return null;
        }

        $administratorMcpToken = $this->administratorMcpTokenRepository->findActiveByPublicTokenId($tokenParts[0]);

        if ($administratorMcpToken === null) {
            return null;
        }

        if (!$this->administratorMcpTokenHasher->verify($administratorMcpToken->getSecretHash(), $tokenParts[1])) {
            return null;
        }

        return $administratorMcpToken;
    }
}
