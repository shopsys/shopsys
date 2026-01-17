<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis;

use Redis;
use Shopsys\FrameworkBundle\Component\Redis\Exception\RedisMultiModeNotSupportedException;

class RedisClientFacade
{
    public function __construct(
        protected readonly Redis $redisClient,
    ) {
    }

    public function save(string $key, mixed $data): void
    {
        $this->redisClient->set($key, $data);
    }

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

    public function delete(string $key): void
    {
        $this->redisClient->del($key);
    }
}
