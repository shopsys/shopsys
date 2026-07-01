<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use Lcobucci\JWT\Token\DataSet;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;

class RefreshTokenRotationFacade
{
    protected const int CACHE_WAIT_ATTEMPTS = 20;

    protected const int CACHE_WAIT_MICROSECONDS = 100000;

    public function __construct(
        protected readonly TokenFacade $tokenFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
        protected readonly TokensDataFactory $tokensDataFactory,
        protected readonly RefreshTokenRedisCacheFacade $refreshTokenRedisCacheFacade,
    ) {
    }

    public function refreshTokens(string $refreshToken): TokensData
    {
        $token = $this->tokenFacade->getTokenByString($refreshToken);

        $this->assertClaimsExists($token->claims());

        $userUuid = $token->claims()->get(FrontendApiUser::CLAIM_UUID);

        try {
            $customerUser = $this->customerUserFacade->getByUuid($userUuid);
        } catch (CustomerUserNotFoundException) {
            throw new InvalidTokenUserMessageException();
        }

        $tokenSecretChain = $token->claims()->get(FrontendApiUser::CLAIM_SECRET_CHAIN);
        $deviceId = $token->claims()->get(FrontendApiUser::CLAIM_DEVICE_ID);

        return $this->getTokensDataOrGenerate($customerUser, $tokenSecretChain, $deviceId);
    }

    protected function getTokensDataOrGenerate(
        CustomerUser $customerUser,
        string $tokenSecretChain,
        string $deviceId,
    ): TokensData {
        $cachedTokensData = $this->refreshTokenRedisCacheFacade->findCachedTokensData($tokenSecretChain);

        if ($cachedTokensData !== null) {
            return $cachedTokensData;
        }

        $lockValue = bin2hex(random_bytes(16));

        $acquiredLock = $this->refreshTokenRedisCacheFacade->acquireLock($tokenSecretChain, $lockValue);

        if (!$acquiredLock) {
            $cachedTokensData = $this->waitForCachedTokensData($tokenSecretChain);

            if ($cachedTokensData !== null) {
                return $cachedTokensData;
            }

            throw new InvalidTokenUserMessageException();
        }

        try {
            $cachedTokensData = $this->refreshTokenRedisCacheFacade->findCachedTokensData($tokenSecretChain);

            if ($cachedTokensData !== null) {
                return $cachedTokensData;
            }

            $tokensData = $this->generateTokensData($customerUser, $tokenSecretChain, $deviceId);
            $this->refreshTokenRedisCacheFacade->saveCachedTokensData(
                $tokenSecretChain,
                $tokensData,
            );

            return $tokensData;
        } finally {
            $this->refreshTokenRedisCacheFacade->releaseLock($tokenSecretChain, $lockValue);
        }
    }

    protected function generateTokensData(
        CustomerUser $customerUser,
        string $tokenSecretChain,
        string $deviceId,
    ): TokensData {
        $customerUserValidRefreshTokenChain = $this->customerUserRefreshTokenChainFacade->findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId(
            $customerUser,
            $tokenSecretChain,
            $deviceId,
        );

        if ($customerUserValidRefreshTokenChain === null) {
            throw new InvalidTokenUserMessageException();
        }

        $tokens = $this->tokensDataFactory->create(
            $this->tokenFacade->createAccessTokenAsString(
                $customerUser,
                $customerUserValidRefreshTokenChain->getDeviceId(),
                $customerUserValidRefreshTokenChain->getAdministrator(),
            ),
            $this->tokenFacade->createRefreshTokenAsString(
                $customerUser,
                $customerUserValidRefreshTokenChain->getDeviceId(),
                $customerUserValidRefreshTokenChain->getAdministrator(),
            ),
        );

        $this->customerUserRefreshTokenChainFacade->removeCustomerRefreshTokenChain(
            $customerUserValidRefreshTokenChain,
        );

        return $tokens;
    }

    protected function assertClaimsExists(DataSet $claims): void
    {
        if (!$claims->has(FrontendApiUser::CLAIM_UUID) || !$claims->has(FrontendApiUser::CLAIM_SECRET_CHAIN) || !$claims->has(FrontendApiUser::CLAIM_DEVICE_ID)) {
            throw new InvalidTokenUserMessageException();
        }
    }

    protected function waitForCachedTokensData(string $tokenSecretChain): ?TokensData
    {
        for ($attempt = 0; $attempt < static::CACHE_WAIT_ATTEMPTS; $attempt++) {
            if (!$this->refreshTokenRedisCacheFacade->isLocked($tokenSecretChain)) {
                return $this->refreshTokenRedisCacheFacade->findCachedTokensData($tokenSecretChain);
            }

            $cachedTokensData = $this->refreshTokenRedisCacheFacade->findCachedTokensData($tokenSecretChain);

            if ($cachedTokensData !== null) {
                return $cachedTokensData;
            }

            usleep(static::CACHE_WAIT_MICROSECONDS);
        }

        return $this->refreshTokenRedisCacheFacade->findCachedTokensData($tokenSecretChain);
    }
}
