<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use DateInterval;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Token\Exception\ExpiredTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\NotVerifiedTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Throwable;

class TokenFacade
{
    protected const int SECRET_CHAIN_LENGTH = 128;

    protected const int ACCESS_TOKEN_EXPIRATION = 300;

    protected const int REFRESH_TOKEN_EXPIRATION = 3600 * 24 * 14;

    public function __construct(
        protected readonly Domain $domain,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly JwtConfigurationProvider $jwtConfigurationProvider,
        protected readonly TokenCustomerUserTransformer $tokenCustomerUserTransformer,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function createAccessTokenAsString(
        CustomerUser $customerUser,
        string $deviceId,
        ?Administrator $administrator = null,
    ): string {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::ACCESS_TOKEN_EXPIRATION, $customerUser->getDomainId())
            ->withClaim(FrontendApiUser::CLAIM_DEVICE_ID, $deviceId)
            ->withClaim(FrontendApiUser::CLAIM_ADMINISTRATOR_UUID, $administrator?->getUuid());

        foreach ($this->tokenCustomerUserTransformer->transform($customerUser) as $key => $value) {
            $tokenBuilder = $tokenBuilder->withClaim($key, $value);
        }

        $jwtConfiguration = $this->jwtConfigurationProvider->getConfiguration();

        return $tokenBuilder
            ->getToken($jwtConfiguration->signer(), $jwtConfiguration->signingKey())
            ->toString();
    }

    public function generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId(
        CustomerUser $customerUser,
        string $secretChain,
        string $deviceId,
    ): UnencryptedToken {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::REFRESH_TOKEN_EXPIRATION, $customerUser->getDomainId())
            ->withClaim(FrontendApiUser::CLAIM_UUID, $customerUser->getUuid())
            ->withClaim(FrontendApiUser::CLAIM_SECRET_CHAIN, $secretChain)
            ->withClaim(FrontendApiUser::CLAIM_DEVICE_ID, $deviceId);

        $jwtConfiguration = $this->jwtConfigurationProvider->getConfiguration();

        return $tokenBuilder->getToken($jwtConfiguration->signer(), $jwtConfiguration->signingKey());
    }

    protected function getTokenBuilderWithExpiration(int $expiration, int $domainId): Builder
    {
        $currentTime = $this->clock->now();
        $expirationTime = $currentTime->add(new DateInterval('PT' . $expiration . 'S'));

        return $this->jwtConfigurationProvider->getConfiguration()
            ->builder(ChainedFormatter::withUnixTimestampDates())
            ->issuedBy($this->domain->getDomainConfigById($domainId)->getBaseUrl())
            ->permittedFor($this->domain->getDomainConfigById($domainId)->getBaseUrl())
            ->issuedAt($currentTime)
            ->canOnlyBeUsedAfter($currentTime)
            ->expiresAt($expirationTime);
    }

    public function getTokenByString(string $tokenString): UnencryptedToken
    {
        try {
            $token = $this->jwtConfigurationProvider->getConfiguration()->parser()->parse($tokenString);

            if (!($token instanceof UnencryptedToken)) {
                throw new InvalidTokenUserMessageException();
            }

            $this->validateToken($token);

            return $token;
        } catch (Throwable $throwable) {
            throw new InvalidTokenUserMessageException();
        }
    }

    public function validateToken(UnencryptedToken $token): void
    {
        $jwtConfiguration = $this->jwtConfigurationProvider->getConfiguration();

        $validator = $jwtConfiguration->validator();

        if (!$validator->validate($token, new StrictValidAt($this->clock))) {
            throw new ExpiredTokenUserMessageException('Token is expired. Please renew.');
        }

        if (!$validator->validate($token, new SignedWith($jwtConfiguration->signer(), $jwtConfiguration->verificationKey()))) {
            throw new NotVerifiedTokenUserMessageException('Token could not be verified.');
        }

        if (!$validator->validate(
            $token,
            new IssuedBy($this->domain->getBaseUrl()),
            new PermittedFor($this->domain->getBaseUrl()),
        )
        ) {
            throw new InvalidTokenUserMessageException();
        }
    }

    public function createRefreshTokenAsString(
        CustomerUser $customerUser,
        string $deviceId,
        ?Administrator $administrator = null,
    ): string {
        $randomChain = sha1(random_bytes(static::SECRET_CHAIN_LENGTH));
        $refreshToken = $this->generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId($customerUser, $randomChain, $deviceId);
        $this->customerUserFacade->addRefreshTokenChain(
            $customerUser,
            $randomChain,
            $deviceId,
            $refreshToken->claims()->get('exp'),
            $administrator,
        );

        return $refreshToken->toString();
    }
}
