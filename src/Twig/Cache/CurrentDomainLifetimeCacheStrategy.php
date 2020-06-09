<?php

declare(strict_types=1);

namespace App\Twig\Cache;

use Asm89\Twig\CacheExtension\CacheStrategyInterface;
use Doctrine\Common\Cache\CacheProvider;

/**
 * @see \Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy
 */
class CurrentDomainLifetimeCacheStrategy implements CacheStrategyInterface
{
    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cacheProvider;

    /**
     * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
     */
    public function __construct(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchBlock($key)
    {
        return $this->cacheProvider->fetch($key['key']);
    }

    /**
     * {@inheritDoc}
     */
    public function generateKey($annotation, $value)
    {
        $key = $annotation;
        if (is_array($value)) {
            $lifetime = $value['lifetime'] ?? 0; /* 0 = infinite lifetime */
            if (array_key_exists('domainId', $value)) {
                $key .= sprintf('__onDomain%d', $value['domainId']);
            }
        } elseif (is_numeric($value)) {
            $lifetime = $value;
        } else {
            throw new \App\Twig\Cache\Exception\InvalidCacheLifetimeException($value);
        }

        return [
            'lifetime' => $lifetime,
            'key' => $key,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function saveBlock($key, $block)
    {
        return $this->cacheProvider->save($key['key'], $block, $key['lifetime']);
    }
}
