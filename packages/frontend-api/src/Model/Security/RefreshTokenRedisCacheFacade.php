<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Redis;
use Shopsys\FrameworkBundle\Component\Redis\Exception\RedisMultiModeNotSupportedException;
use Shopsys\FrontendApiBundle\Model\Security\Exception\InvalidJsonTokenDataException;

class RefreshTokenRedisCacheFacade
{
    protected const int REFRESH_TOKEN_REUSE_GRACE_PERIOD_SECONDS = 10;

    protected const int LOCK_TTL_SECONDS = 10;

    public function __construct(
        protected readonly Redis $redisClient,
        protected readonly TokensDataFactory $tokensDataFactory,
    ) {
    }

    public function saveCachedTokensData(string $secretChain, TokensData $tokensData): void
    {
        $this->redisClient->setex(
            $this->buildCacheKey($secretChain),
            static::REFRESH_TOKEN_REUSE_GRACE_PERIOD_SECONDS,
            $this->createJsonFromTokensData($tokensData),
        );
    }

    public function findCachedTokensData(string $secretChain): ?TokensData
    {
        $value = $this->redisClient->get($this->buildCacheKey($secretChain));

        if ($value instanceof Redis) {
            throw new RedisMultiModeNotSupportedException();
        }

        if ($value === false) {
            return null;
        }

        try {
            return $this->createTokensDataFromJson($value);
        } catch (InvalidJsonTokenDataException) {
            return null;
        }
    }

    public function acquireLock(string $secretChain, string $value): bool
    {
        $result = $this->redisClient->set($this->buildLockKey($secretChain), $value, ['nx', 'ex' => static::LOCK_TTL_SECONDS]);

        if ($result instanceof Redis) {
            throw new RedisMultiModeNotSupportedException();
        }

        return $result === true;
    }

    public function isLocked(string $secretChain): bool
    {
        $result = $this->redisClient->exists($this->buildLockKey($secretChain));

        if ($result instanceof Redis) {
            throw new RedisMultiModeNotSupportedException();
        }

        return $result === 1;
    }

    public function releaseLock(string $secretChain, string $value): void
    {
        $this->redisClient->eval(
            "if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('del', KEYS[1]) else return 0 end",
            [$this->buildLockKey($secretChain), $value],
            1,
        );
    }

    protected function buildCacheKey(string $secretChain): string
    {
        return 'cache:' . hash('sha256', $secretChain);
    }

    protected function buildLockKey(string $secretChain): string
    {
        return 'lock:' . hash('sha256', $secretChain);
    }

    protected function createTokensDataFromJson(string $json): TokensData
    {
        try {
            $data = Json::decode($json, forceArrays: true);
        } catch (JsonException $e) {
            throw new InvalidJsonTokenDataException(previous: $e);
        }

        if (!is_array($data)) {
            throw new InvalidJsonTokenDataException();
        }

        $accessToken = $data['accessToken'] ?? null;
        $refreshToken = $data['refreshToken'] ?? null;

        if (!is_string($accessToken) || !is_string($refreshToken)) {
            throw new InvalidJsonTokenDataException();
        }

        return $this->tokensDataFactory->create($accessToken, $refreshToken);
    }

    protected function createJsonFromTokensData(TokensData $tokensData): string
    {
        return Json::encode(
            [
                'accessToken' => $tokensData->accessToken,
                'refreshToken' => $tokensData->refreshToken,
            ],
        );
    }
}
