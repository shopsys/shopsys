<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis;

use Redis;

class RedisVersionsFacade
{
    /**
     * @var \Redis
     */
    protected $globalClient;

    /**
     * @var string
     */
    protected $currentVersion;

    /**
     * @param \Redis $globalClient
     * @param string $currentVersion
     */
    public function __construct(Redis $globalClient, string $currentVersion)
    {
        $this->globalClient = $globalClient;
        $this->currentVersion = $currentVersion;
    }

    public function cleanOldCache(): void
    {
        $versionPattern = '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]*'; // redis pattern is a glob style and doesn't support repetitions

        $all = $this->globalClient->keys($versionPattern);
        $toRemove = [];

        $prefix = (string)$this->globalClient->getOption(Redis::OPT_PREFIX);
        $currentVersionPrefix = $prefix . $this->currentVersion;
        foreach ($all as $key) {
            if (strpos($key, $currentVersionPrefix) === false) {
                $keyWithoutPrefix = substr($key, strlen($prefix)); // redis returns keys including prefix but needs them without prefix during removing
                $toRemove[] = $keyWithoutPrefix;
            }
        }

        $this->globalClient->del($toRemove);
    }
}
