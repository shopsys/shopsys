<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\User;

use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\UnencryptedToken;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;

class FrontendApiUserFactory implements FrontendApiUserFactoryInterface
{
    /**
     * @param \Lcobucci\JWT\UnencryptedToken $token
     * @return \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser
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

    /**
     * @param \Lcobucci\JWT\Token\DataSet $claims
     */
    protected function assertAllClaimsExists(DataSet $claims): void
    {
        if (
            !$claims->has(FrontendApiUser::CLAIM_UUID) ||
            !$claims->has(FrontendApiUser::CLAIM_FULL_NAME) ||
            !$claims->has(FrontendApiUser::CLAIM_EMAIL) ||
            !$claims->has(FrontendApiUser::CLAIM_DEVICE_ID) ||
            !$claims->has(FrontendApiUser::CLAIM_ROLES) ||
            !$claims->has(FrontendApiUser::CLAIM_ADMINISTRATOR_UUID)
        ) {
            throw new InvalidTokenUserMessageException();
        }
    }
}
