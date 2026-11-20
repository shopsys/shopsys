<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

trait RateLimitTrait
{
    private function clearRateLimiterCachePool(string $rateLimiterCachePoolServiceId): void
    {
        /** @var \Psr\Cache\CacheItemPoolInterface $rateLimiterCachePool */
        $rateLimiterCachePool = self::getContainer()->get($rateLimiterCachePoolServiceId);
        $rateLimiterCachePool->clear();
    }

    private function exhaustRateLimit(string $rateLimiterServiceId, string $key): void
    {
        $rateLimiter = $this->getRateLimiterFactory($rateLimiterServiceId)->create($key);

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
