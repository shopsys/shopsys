<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Shopsys\FrontendApiBundle\Component\HttpFoundation\ClientIpProvider;
use Shopsys\FrontendApiBundle\Model\Customer\User\Exception\TooManyCustomerRegistrationAttemptsUserError;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class CustomerRegistrationRateLimiter
{
    protected const string RATE_LIMITER_KEY_PREFIX = 'customer-registration:';

    public function __construct(
        protected readonly RateLimiterFactoryInterface $customerRegistrationLimiter,
        protected readonly ClientIpProvider $clientIpProvider,
    ) {
    }

    public function consume(): void
    {
        $rateLimit = $this->customerRegistrationLimiter
            ->create(static::RATE_LIMITER_KEY_PREFIX . $this->clientIpProvider->getClientIp())
            ->consume();

        if (!$rateLimit->isAccepted()) {
            throw new TooManyCustomerRegistrationAttemptsUserError('Too many registration attempts. Try again later.');
        }
    }
}
