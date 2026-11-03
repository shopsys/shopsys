<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Tests\FrontendApiBundle\Test\RateLimitTrait;

trait CustomerRegistrationRateLimitTrait
{
    use RateLimitTrait;

    private const string CUSTOMER_REGISTRATION_RATE_LIMITER_SERVICE_ID = 'limiter.frontend_api_customer_registration';

    private const string CUSTOMER_REGISTRATION_RATE_LIMITER_KEY_PREFIX = 'customer-registration:';

    private const string CUSTOMER_REGISTRATION_RATE_LIMITER_CACHE_POOL_SERVICE_ID = 'frontend_api_customer_registration_rate_limiter_cache';

    private const string DIFFERENT_CLIENT_IP = '10.255.0.2';

    private const string TOO_MANY_CUSTOMER_REGISTRATION_ATTEMPTS_USER_CODE = 'too-many-customer-registration-attempts';

    private function clearCustomerRegistrationRateLimits(): void
    {
        $this->clearRateLimiterCachePool(self::CUSTOMER_REGISTRATION_RATE_LIMITER_CACHE_POOL_SERVICE_ID);
    }

    private function exhaustCustomerRegistrationRateLimit(): void
    {
        $this->exhaustRateLimit(self::CUSTOMER_REGISTRATION_RATE_LIMITER_SERVICE_ID, self::CUSTOMER_REGISTRATION_RATE_LIMITER_KEY_PREFIX . $this->getClientIp());
    }

    private function getRemainingCustomerRegistrationRateLimitTokens(): int
    {
        return $this->peekRateLimit(self::CUSTOMER_REGISTRATION_RATE_LIMITER_SERVICE_ID, self::CUSTOMER_REGISTRATION_RATE_LIMITER_KEY_PREFIX . $this->getClientIp())
            ->getRemainingTokens();
    }
}
