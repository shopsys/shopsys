<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Tests\FrontendApiBundle\Test\RateLimitTrait;

trait PasswordRecoveryRateLimitTrait
{
    use RateLimitTrait;

    private const string IP_RATE_LIMITER_SERVICE_ID = 'limiter.frontend_api_password_recovery_ip';

    private const string RATE_LIMITER_CACHE_POOL_SERVICE_ID = 'frontend_api_password_recovery_rate_limiter_cache';

    private const string TOO_MANY_ATTEMPTS_USER_CODE = 'too-many-password-recovery-attempts';

    private function clearRateLimits(): void
    {
        $this->clearRateLimiterCachePool(self::RATE_LIMITER_CACHE_POOL_SERVICE_ID);
    }

    private function exhaustIpRateLimit(): void
    {
        $this->exhaustRateLimit(self::IP_RATE_LIMITER_SERVICE_ID, $this->getClientIp());
    }
}
