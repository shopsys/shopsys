<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

class TokensDataFactory
{
    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @return \Shopsys\FrontendApiBundle\Model\Security\TokensData
     */
    public function create(string $accessToken, string $refreshToken): TokensData
    {
        return new TokensData($accessToken, $refreshToken);
    }
}
