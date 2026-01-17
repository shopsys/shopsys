<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

class TokensData
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
    ) {
    }
}
