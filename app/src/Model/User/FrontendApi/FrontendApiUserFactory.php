<?php

declare(strict_types=1);

namespace App\Model\User\FrontendApi;

use Lcobucci\JWT\UnencryptedToken;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUserFactory as BaseFrontendApiUserFactory;

class FrontendApiUserFactory extends BaseFrontendApiUserFactory
{
    /**
     * @param \Lcobucci\JWT\UnencryptedToken $token
     * @return \App\Model\User\FrontendApi\FrontendApiUser
     */
    public function createFromToken(UnencryptedToken $token): FrontendApiUser
    {
        $this->assertAllClaimsExists($token->claims());

        return new FrontendApiUser(
            $token->claims()->get(FrontendApiUser::CLAIM_UUID),
            $token->claims()->get(FrontendApiUser::CLAIM_FULL_NAME),
            $token->claims()->get(FrontendApiUser::CLAIM_EMAIL),
            $token->claims()->get(FrontendApiUser::CLAIM_DEVICE_ID),
            $token->claims()->get(FrontendApiUser::CLAIM_ROLES),
            $token->claims()->get(FrontendApiUser::CLAIM_ADMINISTRATOR_UUID),
        );
    }
}
