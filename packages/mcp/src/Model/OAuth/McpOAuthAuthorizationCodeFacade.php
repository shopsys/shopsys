<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\OAuth;

use Psr\Cache\CacheItemPoolInterface;

class McpOAuthAuthorizationCodeFacade
{
    protected const int AUTHORIZATION_CODE_TTL_SECONDS = 300;
    protected const string CACHE_KEY_PREFIX = 'mcp_oauth_code_';

    public function __construct(
        protected readonly CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    public function createAuthorizationCode(
        int $administratorId,
        string $clientId,
        string $redirectUri,
        string $codeChallenge,
    ): string {
        $authorizationCode = bin2hex(random_bytes(32));
        $cacheItem = $this->cacheItemPool->getItem($this->getCacheKey($authorizationCode));
        $cacheItem->set([
            'administrator_id' => $administratorId,
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'code_challenge' => $codeChallenge,
        ]);
        $cacheItem->expiresAfter(self::AUTHORIZATION_CODE_TTL_SECONDS);
        $this->cacheItemPool->save($cacheItem);

        return $authorizationCode;
    }

    /**
     * @return array{administrator_id: int, client_id: string, redirect_uri: string, code_challenge: string}|null
     */
    public function consumeAuthorizationCode(string $authorizationCode): ?array
    {
        $cacheItem = $this->cacheItemPool->getItem($this->getCacheKey($authorizationCode));

        if (!$cacheItem->isHit()) {
            return null;
        }

        /** @var array{administrator_id: int, client_id: string, redirect_uri: string, code_challenge: string} $authorizationCodeData */
        $authorizationCodeData = $cacheItem->get();
        $this->cacheItemPool->deleteItem($this->getCacheKey($authorizationCode));

        return $authorizationCodeData;
    }

    protected function getCacheKey(string $authorizationCode): string
    {
        return self::CACHE_KEY_PREFIX . $authorizationCode;
    }
}
