<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis;

use Redis;

class RedisFacade
{
    /**
     * @var \Redis[]
     */
    protected $allClients;

    /**
     * @var \Redis[]
     */
    protected $persistentClients;

    /**
     * @param \Redis[] $allClients
     * @param \Redis[] $persistentClients
     */
    public function __construct(iterable $allClients, iterable $persistentClients = [])
    {
        $this->allClients = $allClients;
        $this->persistentClients = $persistentClients;
    }

    /**
     * @return \Redis[]
     */
    protected function getCacheClients(): iterable
    {
        foreach ($this->allClients as $redis) {
            if (!in_array($redis, $this->persistentClients, true)) {
                yield $redis;
            }
        }
    }

    public function cleanCache(): void
    {
        foreach ($this->getCacheClients() as $redis) {
            $prefix = (string)$redis->getOption(Redis::OPT_PREFIX);
            $pattern = $prefix . '*';
            $this->cleanCacheByScan($redis, $pattern);
        }
    }

    /**
     * @param \Redis $redisClient
     * @param string $pattern
     */
    protected function cleanCacheByScan(Redis $redisClient, string $pattern): void
    {
        $suggestedScanBatchSize = 1000;

        $iterator = null;
        $keys = null;

        while ($keys !== false) {
            $keys = $redisClient->scan($iterator, $pattern, $suggestedScanBatchSize);

            if (is_array($keys) && count($keys) > 0) {
                $redisClient->eval("return redis.call('unlink', unpack(ARGV))", $keys, 0);
            }
        }
    }

    public function pingAllClients(): void
    {
        foreach ($this->allClients as $redis) {
            $redis->ping();
        }
    }
}
