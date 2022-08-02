<?php

declare(strict_types=1);

namespace App\Component\Redis;

use Redis;

class CleanStorefrontCacheFacade
{
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

    public function cleanStorefrontCache(): void
    {
        $prefix = (string)$this->storefrontGraphqlQueryClient->getOption(Redis::OPT_PREFIX);

        $keyPattern = $prefix . '*';

        $iterator = null;
        $toRemove = [];

        do {
            $keys = $this->storefrontGraphqlQueryClient->scan($iterator, $keyPattern);

            if ($keys === false) {
                continue;
            }

            foreach ($keys as $key) {
                $toRemove[] = str_replace($prefix, '', $key);
            }

            $this->storefrontGraphqlQueryClient->unlink($toRemove);
        } while (is_numeric($iterator) && $iterator > 0);
    }
}
