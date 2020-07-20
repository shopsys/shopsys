<?php

declare(strict_types=1);

namespace App\Twig\Cache;

use Doctrine\Common\Cache\CacheProvider;

class TwigCacheFacade
{
    public const SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE = 'SLIGHTLY_CHANGING_PARTS_ON_HOMEPAGE';

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cacheProvider;

    /**
     * @var \App\Twig\Cache\CurrentDomainLifetimeCacheStrategy
     */
    private $currentDomainLifetimeCacheStrategy;

    /**
     * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
     * @param \App\Twig\Cache\CurrentDomainLifetimeCacheStrategy $currentDomainLifetimeCacheStrategy
     */
    public function __construct(
        CacheProvider $cacheProvider,
        CurrentDomainLifetimeCacheStrategy $currentDomainLifetimeCacheStrategy
    ) {
        $this->cacheProvider = $cacheProvider;
        $this->currentDomainLifetimeCacheStrategy = $currentDomainLifetimeCacheStrategy;
    }

    /**
     * @param string $key
     * @param int|null $domainId
     * @throws \App\Twig\Cache\Exception\InvalidCacheLifetimeException
     */
    public function invalidateByKey(string $key, ?int $domainId): void
    {
        $value = [];
        if ($domainId !== null) {
            $value['domainId'] = $domainId;
        }

        $key = $this->currentDomainLifetimeCacheStrategy->generateKey($key, $value);
        $this->cacheProvider->delete($key['key']);
    }
}
