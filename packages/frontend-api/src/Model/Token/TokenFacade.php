<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use BadMethodCallException;
use DateTime;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Token\Exception\ExpiredTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\NotVerifiedTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;

class TokenFacade
{
    protected const SECRET_CHAIN_LENGTH = 128;

    protected const ACCESS_TOKEN_EXPIRATION = 300;

    protected const REFRESH_TOKEN_EXPIRATION = 3600 * 24 * 14;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    protected $parameterBag;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    protected $customerUserFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        Domain $domain,
        ParameterBagInterface $parameterBag,
        CustomerUserFacade $customerUserFacade
    ) {
        $this->domain = $domain;
        $this->parameterBag = $parameterBag;
        $this->customerUserFacade = $customerUserFacade;
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

        return (string)$tokenBuilder->getToken($this->getSigner(), $this->getPrivateKey());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @return \Lcobucci\JWT\Token
     */
    public function generateRefreshTokenByCustomerUserAndSecretChain(CustomerUser $customerUser, string $secretChain): Token
    {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::REFRESH_TOKEN_EXPIRATION);
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_UUID, $customerUser->getUuid());
        $tokenBuilder->withClaim(FrontendApiUser::CLAIM_SECRET_CHAIN, $secretChain);

        return $tokenBuilder->getToken($this->getSigner(), $this->getPrivateKey());
    }

    /**
     * @param int $expiration
     * @return \Lcobucci\JWT\Builder
     */
    protected function getTokenBuilderWithExpiration(int $expiration): Builder
    {
        $currentTime = time();

        return (new Builder())
            ->issuedBy($this->domain->getUrl())
            ->permittedFor($this->domain->getUrl())
            ->issuedAt($currentTime)
            ->expiresAt($currentTime + $expiration);
    }

    /**
     * @return \Lcobucci\JWT\Signer\Key
     */
    public function getPrivateKey(): Key
    {
        return new Key(
            sprintf('file://%s/private.key', $this->parameterBag->get('shopsys.frontend_api.keys_filepath'))
        );
    }

    /**
     * @return \Lcobucci\JWT\Signer\Key
     */
    public function getPublicKey(): Key
    {
        return new Key(
            sprintf('file://%s/public.key', $this->parameterBag->get('shopsys.frontend_api.keys_filepath'))
        );
    }

    /**
     * @return \Lcobucci\JWT\Signer
     */
    public function getSigner(): Signer
    {
        return new Signer\Rsa\Sha256();
    }

    /**
     * @param string $tokenString
     * @return \Lcobucci\JWT\Token
     */
    public function getTokenByString(string $tokenString): Token
    {
        try {
            return (new Parser())->parse($tokenString);
        } catch (Throwable $throwable) {
            throw new InvalidTokenUserMessageException('Token is not valid.');
        }
    }

    /**
     * @param \Lcobucci\JWT\Token $token
     */
    public function validateToken(Token $token): void
    {
        $validationData = new ValidationData();
        $validationData->setAudience($this->domain->getUrl());
        $validationData->setIssuer($this->domain->getUrl());

        if ($token->isExpired()) {
            throw new ExpiredTokenUserMessageException('Token is expired. Please renew.');
        }

        if (!$token->validate($validationData)) {
            throw new InvalidTokenUserMessageException('Token is not valid.');
        }

        try {
            if (!$token->verify($this->getSigner(), $this->getPublicKey())) {
                throw new NotVerifiedTokenUserMessageException('Token could not be verified.');
            }
        } catch (BadMethodCallException $badMethodCallException) {
            throw new InvalidTokenUserMessageException('Token is not valid.');
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
            DateTime::createFromFormat('U', '' . $refreshToken->getClaim('exp'))
        );

        return (string)$refreshToken;
    }
}
