<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

class TokensData
{
    /**
     * @param string $accessToken
     * @param string $refreshToken
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
    ) {
    }
}
