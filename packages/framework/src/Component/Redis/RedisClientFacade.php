<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis;

use Redis;

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
