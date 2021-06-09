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
            $token->claims()->get(FrontendApiUser::CLAIM_UUID),
            $token->claims()->get(FrontendApiUser::CLAIM_FULL_NAME),
            $token->claims()->get(FrontendApiUser::CLAIM_EMAIL),
            $token->claims()->get(FrontendApiUser::CLAIM_DEVICE_ID),
            $token->claims()->get(FrontendApiUser::CLAIM_ROLES)
        );
    }
}
