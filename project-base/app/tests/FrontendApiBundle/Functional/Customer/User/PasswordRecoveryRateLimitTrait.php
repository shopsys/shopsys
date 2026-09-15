<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

trait PasswordRecoveryRateLimitTrait
{
    private const string IP_RATE_LIMITER_SERVICE_ID = 'limiter.frontend_api_password_recovery_ip';

    private const string RATE_LIMITER_CACHE_POOL_SERVICE_ID = 'frontend_api_password_recovery_rate_limiter_cache';

    private const string TOO_MANY_ATTEMPTS_USER_CODE = 'too-many-password-recovery-attempts';

    private function clearRateLimits(): void
    {
        /** @var \Psr\Cache\CacheItemPoolInterface $rateLimiterCachePool */
        $rateLimiterCachePool = self::getContainer()->get(self::RATE_LIMITER_CACHE_POOL_SERVICE_ID);
        $rateLimiterCachePool->clear();
    }

    private function exhaustIpRateLimit(): void
    {
        $rateLimiter = $this->getRateLimiterFactory(self::IP_RATE_LIMITER_SERVICE_ID)->create($this->getClientIp());

        do {
            $rateLimit = $rateLimiter->consume();
        } while ($rateLimit->isAccepted());
    }

    private function peekRateLimit(string $rateLimiterServiceId, string $key): RateLimit
    {
        return $this->getRateLimiterFactory($rateLimiterServiceId)->create($key)->consume(0);
    }

    private function getClientIp(): string
    {
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = self::getCurrentClient()->getRequest();

        return (string)$request->getClientIp();
    }

    private function getRateLimiterFactory(string $rateLimiterServiceId): RateLimiterFactoryInterface
    {
        /** @var \Symfony\Component\RateLimiter\RateLimiterFactoryInterface $rateLimiterFactory */
        $rateLimiterFactory = self::getContainer()->get($rateLimiterServiceId);

        return $rateLimiterFactory;
    }
}
