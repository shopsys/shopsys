<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Lcobucci\JWT\Token;

class FrontendApiUserFactory implements FrontendApiUserFactoryInterface
{
    /**
     * @param \Lcobucci\JWT\Token $token
     * @return \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser
     */
    public function createFromToken(Token $token): FrontendApiUser
    {
        return new FrontendApiUser(
            $token->getClaim('uuid'),
            $token->getClaim('fullName'),
            $token->getClaim('email'),
            $token->getClaim('roles')
        );
    }
}
