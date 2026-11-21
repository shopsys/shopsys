<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cache;

use Override;
use Shopsys\FrameworkBundle\Component\Cache\Exception\NamespaceCacheKeyNotFoundException;
use Shopsys\FrameworkBundle\Component\Cache\Exception\ValueCacheKeyNotFoundException;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * The cache is read many times in a single request, so the values are kept in plain arrays
 * (a read is two array lookups instead of several cache item instances and expiration checks of a cache adapter).
 */
class InMemoryCache implements ResettableInterface
{
    protected const string NOT_ALLOWED_CHARS = ItemInterface::RESERVED_CHARACTERS . '".';

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $valuesByNamespace = [];

    /**
     * @var string[]
     */
    protected array $notAllowedChars;

    public function __construct()
    {
        $this->notAllowedChars = str_split(static::NOT_ALLOWED_CHARS);
    }

    public function getItem(string $namespace, mixed ...$keyParts): mixed
    {
        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            throw new NamespaceCacheKeyNotFoundException($namespace);
        }

        $key = $this->generateKey($keyParts);

        if (!array_key_exists($key, $this->valuesByNamespace[$namespace])) {
            throw new ValueCacheKeyNotFoundException($namespace, $key);
        }

        return $this->valuesByNamespace[$namespace][$key];
    }

    /**
     * @return array<string, mixed>
     */
    public function getValuesByNamespace(string $namespace): array
    {
        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            throw new NamespaceCacheKeyNotFoundException($namespace);
        }

        return $this->valuesByNamespace[$namespace];
    }

    public function hasItem(string $namespace, mixed ...$keyParts): bool
    {
        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        return $this->hasNamespaceCache($namespace)
            && array_key_exists($this->generateKey($keyParts), $this->valuesByNamespace[$namespace]);
    }

    protected function hasNamespaceCache(string $namespace): bool
    {
        return array_key_exists($namespace, $this->valuesByNamespace);
    }

    #[Override]
    public function reset(): void
    {
        $this->valuesByNamespace = [];
    }

    public function deleteItem(string $namespace, mixed ...$keyParts): void
    {
        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            return;
        }

        unset($this->valuesByNamespace[$namespace][$this->generateKey($keyParts)]);
    }

    public function deleteAllItemsInNamespace(string $namespace): void
    {
        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);

        if (!$this->hasNamespaceCache($namespace)) {
            return;
        }

        $this->valuesByNamespace[$namespace] = [];
    }

    public function save(string $namespace, mixed $value, mixed ...$keyParts): void
    {
        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);
        $key = $this->generateKey($keyParts);

        if (!$this->hasNamespaceCache($namespace)) {
            $this->valuesByNamespace[$namespace] = [];
        }

        if (is_callable($value)) {
            $value = $value();
        }

        $this->valuesByNamespace[$namespace][$key] = $value;
    }

    protected function replaceNotAllowedCharactersInKey(string $key): string
    {
        if (strpbrk($key, static::NOT_ALLOWED_CHARS) === false) {
            return $key;
        }

        return str_replace($this->notAllowedChars, '~', $key);
    }

    /**
     * @template T
     * @param T|callable(): T $value
     * @return T
     */
    public function getOrSaveValue(string $namespace, mixed $value, mixed ...$keyParts): mixed
    {
        $namespace = $this->replaceNotAllowedCharactersInKey($namespace);
        $key = $this->generateKey($keyParts);

        if ($this->hasNamespaceCache($namespace) && array_key_exists($key, $this->valuesByNamespace[$namespace])) {
            return $this->valuesByNamespace[$namespace][$key];
        }

        if (is_callable($value)) {
            $value = $value();
        }

        $this->valuesByNamespace[$namespace][$key] = $value;

        return $value;
    }

    protected function generateKey(array $keyParts): string
    {
        return $this->replaceNotAllowedCharactersInKey(implode('~', $keyParts));
    }

    public function getKey(mixed ...$keyParts): string
    {
        return $this->generateKey($keyParts);
    }
}
