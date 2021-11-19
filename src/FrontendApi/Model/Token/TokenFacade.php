<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Token;

use App\FrontendApi\Exception\DeprecatedMethodException;
use DateTime;
use Lcobucci\JWT\Token;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade as BaseTokenFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;

/**
 * @method string createAccessTokenAsString(\App\Model\Customer\User\CustomerUser $customerUser, string $deviceId)
 */
class TokenFacade extends BaseTokenFacade
{
    // temporarily increased expiration until FWCC-581 is resolved
    protected const ACCESS_TOKEN_EXPIRATION = 3600 * 24 * 14;

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $deviceId
     * @return string
     */
    public function createRefreshTokenAsString(CustomerUser $customerUser, string $deviceId): string
    {
        $randomChain = sha1(random_bytes(static::SECRET_CHAIN_LENGTH));
        $refreshToken = $this->generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId(
            $customerUser,
            $randomChain,
            $deviceId
        );

        $this->customerUserFacade->addRefreshTokenChain(
            $customerUser,
            $randomChain,
            $deviceId,
            DateTime::createFromImmutable($refreshToken->claims()->get('exp'))
        );

        return $refreshToken->toString();
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @param string $deviceId
     * @return \Lcobucci\JWT\Token
     */
    public function generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId(
        CustomerUser $customerUser,
        string $secretChain,
        string $deviceId
    ): Token {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::REFRESH_TOKEN_EXPIRATION);
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_UUID, $customerUser->getUuid());
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_SECRET_CHAIN, $secretChain);
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_DEVICE_ID, $deviceId);

        return $tokenBuilder->getToken($this->getSigner(), $this->getPrivateKey());
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @return \Lcobucci\JWT\Token
     * @deprecated Method is deprecated. Use "generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId()" instead.
     */
    public function generateRefreshTokenByCustomerUserAndSecretChain(CustomerUser $customerUser, string $secretChain): Token
    {
        throw new DeprecatedMethodException();
    }
}
