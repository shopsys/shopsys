<?php

declare(strict_types=1);

namespace App\Twig\Cache;

use Asm89\Twig\CacheExtension\CacheStrategyInterface;
use Doctrine\Common\Cache\CacheProvider;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

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
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(CacheProvider $cacheProvider, Domain $domain)
    {
        $this->cacheProvider = $cacheProvider;
        $this->domain = $domain;
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
        if (!is_numeric($value)) {
            throw new \App\Twig\Cache\Exception\InvalidCacheLifetimeException($value);
        }

        return [
            'lifetime' => $value,
            'key' => sprintf('%s__onDomain%d', $annotation, $this->domain->getId()),
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
