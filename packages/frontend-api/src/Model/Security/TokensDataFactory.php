<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

class TokensDataFactory
{
    public function create(string $accessToken, string $refreshToken): TokensData
    {
        return new TokensData($accessToken, $refreshToken);
    }
}
