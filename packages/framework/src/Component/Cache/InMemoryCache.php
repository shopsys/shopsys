<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cache;

use Override;
use Shopsys\FrameworkBundle\Component\Cache\Exception\NamespaceCacheKeyNotFoundException;
use Shopsys\FrameworkBundle\Component\Cache\Exception\ValueCacheKeyNotFoundException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Contracts\Cache\ItemInterface;

class InMemoryCache implements ResettableInterface
{
    protected const string NOT_ALLOWED_CHARS = ItemInterface::RESERVED_CHARACTERS . '".';

    protected ArrayAdapter $namespacesCache;

    public function __construct()
    {
        $this->namespacesCache = new ArrayAdapter(0, false);
    }

    public function getItem(string $namespace, mixed ...$keyParts): mixed
    {
        $key = $this->generateKey($keyParts);

        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            throw new NamespaceCacheKeyNotFoundException($namespace);
        }

        $key = $this->replaceNotAllowedCharactersInKey($key);

        if (!$this->hasItem($namespace, $key)) {
            throw new ValueCacheKeyNotFoundException($namespace, $key);
        }

        return $this->getNamespaceCache($namespace)->getItem($key)->get();
    }

    public function getValuesByNamespace(string $namespace): array
    {
        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            throw new NamespaceCacheKeyNotFoundException($namespace);
        }

        return $this->getNamespaceCache($namespace)->getValues();
    }

    protected function getNamespaceCache(string $namespace): ArrayAdapter
    {
        return $this->namespacesCache->getItem($namespace)->get();
    }

    public function hasItem(string $namespace, mixed ...$keyParts): bool
    {
        $key = $this->generateKey($keyParts);

        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        if ($this->hasNamespaceCache($namespace)) {
            $key = $this->replaceNotAllowedCharactersInKey($key);

            return $this->getNamespaceCache($namespace)->hasItem($key);
        }

        return false;
    }

    protected function hasNamespaceCache(string $namespace): bool
    {
        return $this->namespacesCache->hasItem($namespace);
    }

    #[Override]
    public function reset(): void
    {
        $this->namespacesCache->reset();
    }

    public function deleteItem(string $namespace, mixed ...$keyParts): void
    {
        $key = $this->generateKey($keyParts);

        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            return;
        }

        $key = $this->replaceNotAllowedCharactersInKey($key);
        $namespaceCache = $this->getNamespaceCache($namespace);
        $namespaceCache->deleteItem($key);
    }

    public function deleteAllItemsInNamespace(string $namespace): void
    {
        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            return;
        }

        $namespaceCache = $this->getNamespaceCache($namespace);
        $namespaceCache->clear();
    }

    public function save(string $namespace, mixed $value, mixed ...$keyParts): void
    {
        $key = $this->generateKey($keyParts);

        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);
        $key = $this->replaceNotAllowedCharactersInKey($key);
        $namespaceCacheItem = $this->namespacesCache->getItem($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            $namespaceCacheItem->set(new ArrayAdapter(0, false));
            $this->namespacesCache->save($namespaceCacheItem);
        }

        /** @var \Symfony\Component\Cache\Adapter\ArrayAdapter $namespaceCache */
        $namespaceCache = $namespaceCacheItem->get();
        $valueItem = $namespaceCache->getItem($key);

        if (is_callable($value)) {
            $value = $value();
        }

        $valueItem->set($value);
        $namespaceCache->save($valueItem);
    }

    protected function replaceNotAllowedCharactersInKey(string $key): string
    {
        foreach (str_split(static::NOT_ALLOWED_CHARS) as $char) {
            $key = str_replace($char, '~', $key);
        }

        return $key;
    }

    /**
     * @template T
     * @param T|callable(): T $value
     * @return T
     */
    public function getOrSaveValue(string $namespace, mixed $value, mixed ...$keyParts): mixed
    {
        $key = $this->generateKey($keyParts);

        if ($this->hasItem($namespace, $key)) {
            return $this->getItem($namespace, $key);
        }

        if (is_callable($value)) {
            $value = $value();
        }

        $this->save($namespace, $value, $key);

        return $value;
    }

    protected function generateKey(array $keyParts): string
    {
        $key = implode('~', $keyParts);

        return $this->replaceNotAllowedCharactersInKey($key);
    }

    public function getKey(mixed ...$keyParts): string
    {
        return $this->generateKey($keyParts);
    }
}
