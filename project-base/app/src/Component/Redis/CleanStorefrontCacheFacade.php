<?php

declare(strict_types=1);

namespace App\Component\Redis;

use Redis;

class CleanStorefrontCacheFacade
{
    public const NAVIGATION_QUERY_KEY_PART = 'NavigationQuery';

    /**
     * @var \Redis
     */
    private Redis $storefrontGraphqlQueryClient;

    /**
     * @param \Redis $storefrontGraphqlQueryClient
     */
    public function __construct(
        Redis $storefrontGraphqlQueryClient
    ) {
        $this->storefrontGraphqlQueryClient = $storefrontGraphqlQueryClient;
    }

    /**
     * @param string $queryKey
     */
    public function cleanStorefrontGraphqlQueryCache(string $queryKey = ''): void
    {
        $prefix = (string)$this->storefrontGraphqlQueryClient->getOption(Redis::OPT_PREFIX);

        $keyPattern = $prefix . $queryKey . '*';
        $iterator = null;
        $toRemove = [];

        do {
            $keys = $this->storefrontGraphqlQueryClient->scan($iterator, $keyPattern, 0);

            if ($keys === false || count($keys) === 0) {
                continue;
            }

            foreach ($keys as $key) {
                $toRemove[] = str_replace($prefix, '', $key);
            }
        } while (is_numeric($iterator) && $iterator > 0);

        $this->storefrontGraphqlQueryClient->unlink($toRemove);
    }
}
