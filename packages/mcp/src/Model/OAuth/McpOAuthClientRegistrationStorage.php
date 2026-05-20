<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\OAuth;

use Psr\Cache\CacheItemPoolInterface;

class McpOAuthClientRegistrationStorage
{
    protected const int REGISTRATION_TTL_SECONDS = 2592000;
    protected const string CACHE_KEY_PREFIX = 'mcp_oauth_client_';

    public function __construct(
        protected readonly CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    public function save(McpOAuthClientRegistrationData $clientRegistrationData): void
    {
        $cacheItem = $this->cacheItemPool->getItem($this->getCacheKey($clientRegistrationData->clientId));
        $cacheItem->set($clientRegistrationData->toArray());
        $cacheItem->expiresAfter(static::REGISTRATION_TTL_SECONDS);
        $this->cacheItemPool->save($cacheItem);
    }

    public function findByClientId(string $clientId): ?McpOAuthClientRegistrationData
    {
        $cacheItem = $this->cacheItemPool->getItem($this->getCacheKey($clientId));

        if (!$cacheItem->isHit()) {
            return null;
        }

        /** @var array{client_id: string, client_name: string, redirect_uris: array<string>} $registrationData */
        $registrationData = $cacheItem->get();

        return McpOAuthClientRegistrationData::createFromArray($registrationData);
    }

    protected function getCacheKey(string $clientId): string
    {
        return self::CACHE_KEY_PREFIX . $clientId;
    }
}
