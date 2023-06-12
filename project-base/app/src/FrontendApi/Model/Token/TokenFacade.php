<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Token;

use App\Component\Deprecation\DeprecatedMethodException;
use App\Model\Administrator\Administrator;
use App\Model\User\FrontendApi\FrontendApiUser;
use DateTime;
use Lcobucci\JWT\UnencryptedToken;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrontendApiBundle\Model\Token\TokenCustomerUserTransformer;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade as BaseTokenFacade;

/**
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 */
class TokenFacade extends BaseTokenFacade
{
    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $deviceId
     * @param \App\Model\Administrator\Administrator|null $administrator
     * @return string
     */
    public function createAccessTokenAsString(
        CustomerUser $customerUser,
        string $deviceId,
        ?Administrator $administrator = null,
    ): string {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::ACCESS_TOKEN_EXPIRATION);
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_DEVICE_ID, $deviceId);
        $tokenBuilder->withClaim(
            FrontendApiUser::CLAIM_ADMINISTRATOR_UUID,
            $administrator !== null ? $administrator->getUuid() : null,
        );

        foreach (TokenCustomerUserTransformer::transform($customerUser) as $key => $value) {
            $tokenBuilder->withClaim($key, $value);
        }

        return $tokenBuilder
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey())
            ->toString();
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $deviceId
     * @param \App\Model\Administrator\Administrator|null $administrator
     * @return string
     */
    public function createRefreshTokenAsString(
        CustomerUser $customerUser,
        string $deviceId,
        ?Administrator $administrator = null,
    ): string {
        $randomChain = sha1(random_bytes(static::SECRET_CHAIN_LENGTH));
        $refreshToken = $this->generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId(
            $customerUser,
            $randomChain,
            $deviceId,
        );

        $this->customerUserFacade->addRefreshTokenChain(
            $customerUser,
            $randomChain,
            $deviceId,
            DateTime::createFromImmutable($refreshToken->claims()->get('exp')),
            $administrator,
        );

        return $refreshToken->toString();
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @param string $deviceId
     * @return \Lcobucci\JWT\UnencryptedToken
     */
    public function generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId(
        CustomerUser $customerUser,
        string $secretChain,
        string $deviceId,
    ): UnencryptedToken {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::REFRESH_TOKEN_EXPIRATION);
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_UUID, $customerUser->getUuid());
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_SECRET_CHAIN, $secretChain);
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_DEVICE_ID, $deviceId);

        return $tokenBuilder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @return \Lcobucci\JWT\UnencryptedToken
     * @deprecated Method is deprecated. Use "generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId()" instead.
     */
    public function generateRefreshTokenByCustomerUserAndSecretChain(
        CustomerUser $customerUser,
        string $secretChain,
    ): UnencryptedToken {
        throw new DeprecatedMethodException();
    }
}
