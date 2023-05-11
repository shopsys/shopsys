<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Token\Exception\ExpiredTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\NotVerifiedTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Throwable;
use function date_default_timezone_get;

class TokenFacade
{
    protected const SECRET_CHAIN_LENGTH = 128;

    protected const ACCESS_TOKEN_EXPIRATION = 300;

    protected const REFRESH_TOKEN_EXPIRATION = 3600 * 24 * 14;

    protected Domain $domain;

    protected CustomerUserFacade $customerUserFacade;

    protected Configuration $jwtConfiguration;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Lcobucci\JWT\Configuration $jwtConfiguration
     */
    public function __construct(
        Domain $domain,
        CustomerUserFacade $customerUserFacade,
        Configuration $jwtConfiguration
    ) {
        $this->domain = $domain;
        $this->customerUserFacade = $customerUserFacade;
        $this->jwtConfiguration = $jwtConfiguration;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $deviceId
     * @return string
     */
    public function createAccessTokenAsString(CustomerUser $customerUser, string $deviceId): string
    {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::ACCESS_TOKEN_EXPIRATION);

        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_DEVICE_ID, $deviceId);
        foreach (TokenCustomerUserTransformer::transform($customerUser) as $key => $value) {
            $tokenBuilder->withClaim($key, $value);
        }

        return $tokenBuilder
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey())
            ->toString();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @return \Lcobucci\JWT\UnencryptedToken
     */
    public function generateRefreshTokenByCustomerUserAndSecretChain(CustomerUser $customerUser, string $secretChain): UnencryptedToken
    {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::REFRESH_TOKEN_EXPIRATION);
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_UUID, $customerUser->getUuid());
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_SECRET_CHAIN, $secretChain);

        return $tokenBuilder->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

    /**
     * @param int $expiration
     * @return \Lcobucci\JWT\Builder
     */
    protected function getTokenBuilderWithExpiration(int $expiration): Builder
    {
        $currentTime = new DateTimeImmutable();
        $expirationTime = $currentTime->add(new DateInterval('PT' . $expiration . 'S'));

        return $this->jwtConfiguration->builder(ChainedFormatter::withUnixTimestampDates())
            ->issuedBy($this->domain->getUrl())
            ->permittedFor($this->domain->getUrl())
            ->issuedAt($currentTime)
            ->canOnlyBeUsedAfter($currentTime)
            ->expiresAt($expirationTime);
    }

    /**
     * @param string $tokenString
     * @return \Lcobucci\JWT\UnencryptedToken
     */
    public function getTokenByString(string $tokenString): UnencryptedToken
    {
        try {
            $token = $this->jwtConfiguration->parser()->parse($tokenString);

            if (!($token instanceof UnencryptedToken)) {
                throw new InvalidTokenUserMessageException();
            }

            $this->validateToken($token);

            return $token;
        } catch (Throwable $throwable) {
            throw new InvalidTokenUserMessageException();
        }
    }

    /**
     * @param \Lcobucci\JWT\UnencryptedToken $token
     */
    public function validateToken(UnencryptedToken $token): void
    {
        $validator = $this->jwtConfiguration->validator();

        if (!$validator->validate($token, new StrictValidAt(new SystemClock(new DateTimeZone(date_default_timezone_get()))))) {
            throw new ExpiredTokenUserMessageException('Token is expired. Please renew.');
        }

        if (!$validator->validate($token, new SignedWith($this->jwtConfiguration->signer(), $this->jwtConfiguration->verificationKey()))) {
            throw new NotVerifiedTokenUserMessageException('Token could not be verified.');
        }

        if (!$validator->validate(
            $token,
            new IssuedBy($this->domain->getUrl()),
            new PermittedFor($this->domain->getUrl())
        )
        ) {
            throw new InvalidTokenUserMessageException();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $deviceId
     * @return string
     */
    public function createRefreshTokenAsString(CustomerUser $customerUser, string $deviceId): string
    {
        $randomChain = sha1(random_bytes(static::SECRET_CHAIN_LENGTH));
        $refreshToken = $this->generateRefreshTokenByCustomerUserAndSecretChain($customerUser, $randomChain);
        $this->customerUserFacade->addRefreshTokenChain(
            $customerUser,
            $randomChain,
            $deviceId,
            DateTime::createFromImmutable($refreshToken->claims()->get('exp'))
        );

        return $refreshToken->toString();
    }
}
