<?php

declare(strict_types=1);

namespace App\Model\User\FrontendApi;

use Lcobucci\JWT\Token;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUserFactory as BaseFrontendApiUserFactory;

class FrontendApiUserFactory extends BaseFrontendApiUserFactory
{
    /**
     * @param \Lcobucci\JWT\Token $token
     * @return \App\Model\User\FrontendApi\FrontendApiUser
     */
    public function createFromToken(Token $token): FrontendApiUser
    {
        $claims = $token->claims();

        return new FrontendApiUser(
            $claims->get(FrontendApiUser::CLAIM_UUID),
            $claims->get(FrontendApiUser::CLAIM_FULL_NAME),
            $claims->get(FrontendApiUser::CLAIM_EMAIL),
            $claims->get(FrontendApiUser::CLAIM_DEVICE_ID),
            $claims->get(FrontendApiUser::CLAIM_ROLES),
            $claims->get(FrontendApiUser::CLAIM_ADMINISTRATOR_UUID),
        );
    }
}
