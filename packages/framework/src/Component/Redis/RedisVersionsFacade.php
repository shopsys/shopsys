<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis;

use Redis;

class RedisVersionsFacade
{
    /**
     * @param \Redis $globalClient
     * @param string $currentVersion
     */
    public function __construct(protected readonly Redis $globalClient, protected readonly string $currentVersion)
    {
    }

    public function cleanOldCache(): void
    {
        $prefix = (string)$this->globalClient->getOption(Redis::OPT_PREFIX);

        $versionPattern = $prefix . '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]*'; // redis pattern is a glob style and doesn't support repetitions
        $currentVersionPrefix = $prefix . $this->currentVersion;

        /** @var int|null $iterator */
        $iterator = null;

        do {
            $keys = $this->globalClient->scan($iterator, $versionPattern, 0);

            if ($keys === false) {
                continue;
            }

            $toRemove = [];

            foreach ($keys as $key) {
                if (strpos($key, $currentVersionPrefix) === false) {
                    $keyWithoutPrefix = substr(
                        $key,
                        strlen($prefix)
                    ); // redis returns keys including prefix but needs them without prefix during removing
                    $toRemove[] = $keyWithoutPrefix;
                }
            }
            $this->globalClient->unlink($toRemove);
        } while (is_numeric($iterator) && $iterator > 0);
    }
}
