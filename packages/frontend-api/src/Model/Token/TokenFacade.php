<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use BadMethodCallException;
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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;

class TokenFacade
{
    protected const SECRET_CHAIN_LENGTH = 128;

    protected const ACCESS_TOKEN_EXPIRATION = 3600;

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
     * @return string
     */
    public function generateAccessTokenByCustomerUser(CustomerUser $customerUser): string
    {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::ACCESS_TOKEN_EXPIRATION);

        foreach (TokenCustomerUserTransformer::transform($customerUser) as $key => $value) {
            $tokenBuilder->withClaim($key, $value);
        }

        return (string)$tokenBuilder->getToken($this->getSigner(), $this->getKey());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $secretChain
     * @return \Lcobucci\JWT\Token
     */
    public function generateRefreshTokenByCustomerUser(CustomerUser $customerUser, string $secretChain): Token
    {
        $tokenBuilder = $this->getTokenBuilderWithExpiration(static::REFRESH_TOKEN_EXPIRATION);
        $tokenBuilder->withClaim('uuid', $customerUser->getUuid());
        $tokenBuilder->withClaim('secretChain', $secretChain);

        return $tokenBuilder->getToken($this->getSigner(), $this->getKey());
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
    public function getKey(): Key
    {
        return new Key($this->parameterBag->get('secret'));
    }

    /**
     * @return \Lcobucci\JWT\Signer
     */
    public function getSigner(): Signer
    {
        return new Signer\Hmac\Sha256();
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
            if (!$token->verify($this->getSigner(), $this->getKey())) {
                throw new NotVerifiedTokenUserMessageException('Token could not be verified.');
            }
        } catch (BadMethodCallException $badMethodCallException) {
            throw new InvalidTokenUserMessageException('Token is not valid.');
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string
     */
    public function createRefreshTokenAsString(CustomerUser $customerUser): string
    {
        $randomChain = sha1(random_bytes(static::SECRET_CHAIN_LENGTH));
        $refreshToken = $this->generateRefreshTokenByCustomerUser($customerUser, $randomChain);
        $this->customerUserFacade->addRefreshTokenChain(
            $customerUser,
            $randomChain,
            \DateTime::createFromFormat('U', '' . $refreshToken->getClaim('exp'))
        );

        return (string)$refreshToken;
    }
}
