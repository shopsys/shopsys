<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis;

use Redis;
use Shopsys\FrameworkBundle\Component\Redis\Exception\RedisMultiModeNotSupportedException;

class RedisClientFacade
{
    /**
     * @param \Redis $redisClient
     */
    public function __construct(
        protected readonly Redis $redisClient,
    ) {
    }

    /**
     * @param string $key
     * @param mixed $data
     */
    public function save(string $key, mixed $data): void
    {
        $this->redisClient->set($key, $data);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function contains(string $key): bool
    {
        $exists = $this->redisClient->exists($key);

        if ($exists instanceof Redis) {
            throw new RedisMultiModeNotSupportedException();
        }

        if (is_bool($exists)) {
            return $exists;
        }

        return $exists > 0;
    }

    /**
     * @param string $key
     */
    public function delete(string $key): void
    {
        $this->redisClient->del($key);
    }
}
