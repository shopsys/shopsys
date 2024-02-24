<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\LocalCache;

use Shopsys\FrameworkBundle\Component\LocalCache\Exception\NamespaceCacheKeyNotFoundException;
use Shopsys\FrameworkBundle\Component\LocalCache\Exception\ValueCacheKeyNotFoundException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Service\ResetInterface;

class LocalCacheFacade implements ResetInterface
{
    protected ArrayAdapter $namespacesCache;

    public function __construct()
    {
        $this->namespacesCache = new ArrayAdapter(0, false);
    }

    /**
     * @param string $namespace
     * @param string $key
     * @return mixed
     */
    public function getItem(string $namespace, string $key): mixed
    {
        if (!$this->hasNamespaceCache($namespace)) {
            throw new NamespaceCacheKeyNotFoundException($namespace);
        }

        if (!$this->hasItem($namespace, $key)) {
            throw new ValueCacheKeyNotFoundException($namespace, $key);
        }

        return $this->getNamespaceCache($namespace)->getItem($key)->get();
    }

    /**
     * @param string $namespace
     * @return array
     */
    public function getValuesByNamespace(string $namespace): array
    {
        if (!$this->hasNamespaceCache($namespace)) {
            throw new NamespaceCacheKeyNotFoundException($namespace);
        }

        return $this->getNamespaceCache($namespace)->getValues();
    }

    /**
     * @param string $namespace
     * @return \Symfony\Component\Cache\Adapter\ArrayAdapter
     */
    protected function getNamespaceCache(string $namespace): ArrayAdapter
    {
        return $this->namespacesCache->getItem($namespace)->get();
    }

    /**
     * @param string $namespace
     * @param string $key
     * @return bool
     */
    public function hasItem(string $namespace, string $key): bool
    {
        if ($this->hasNamespaceCache($namespace)) {
            return $this->getNamespaceCache($namespace)->hasItem($key);
        }

        return false;
    }

    /**
     * @param string $namespace
     * @return bool
     */
    protected function hasNamespaceCache(string $namespace): bool
    {
        return $this->namespacesCache->hasItem($namespace);
    }

    public function reset(): void
    {
        $this->namespacesCache->reset();
    }

    /**
     * @param string $namespace
     * @param string $key
     */
    public function deleteItem(string $namespace, string $key): void
    {
        if (!$this->hasNamespaceCache($namespace)) {
            return;
        }

        $namespaceCache = $this->getNamespaceCache($namespace);
        $namespaceCache->deleteItem($key);
    }

    /**
     * @param string $namespace
     * @param string $key
     * @param mixed $value
     */
    public function save(string $namespace, string $key, mixed $value): void
    {
        $namespaceCacheItem = $this->namespacesCache->getItem($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            $namespaceCacheItem->set(new ArrayAdapter(0, false));
            $this->namespacesCache->save($namespaceCacheItem);
        }

        /** @var \Symfony\Component\Cache\Adapter\ArrayAdapter $namespaceCache */
        $namespaceCache = $namespaceCacheItem->get();
        $valueItem = $namespaceCache->getItem($key);
        $valueItem->set($value);
        $namespaceCache->save($valueItem);
    }
}
