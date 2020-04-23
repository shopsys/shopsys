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
            $token->getClaim(FrontendApiUser::CLAIM_UUID),
            $token->getClaim(FrontendApiUser::CLAIM_FULL_NAME),
            $token->getClaim(FrontendApiUser::CLAIM_EMAIL),
            $token->getClaim(FrontendApiUser::CLAIM_DEVICE_ID),
            $token->getClaim(FrontendApiUser::CLAIM_ROLES)
        );
    }
}
