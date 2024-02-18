<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\TwoLevelCache;

use Shopsys\FrameworkBundle\Component\TwoLevelCache\Exception\FirstLevelCacheKeyNotFoundException;
use Shopsys\FrameworkBundle\Component\TwoLevelCache\Exception\SecondLevelCacheKeyNotFoundException;
use Symfony\Contracts\Service\ResetInterface;

class TwoLevelCacheProvider implements ResetInterface
{
    /**
     * @var mixed[][]
     */
    protected array $twoLevelCache = [];

    /**
     * @param string $firstLevelKey
     * @param string $secondLevelKey
     * @param mixed $value
     */
    public function add(string $firstLevelKey, string $secondLevelKey, mixed $value): void
    {
        $this->twoLevelCache[$firstLevelKey][$secondLevelKey] = $value;
    }

    /**
     * @param string $firstLevelKey
     * @param string|int $secondLevelKey
     * @return mixed
     */
    public function find(string $firstLevelKey, string|int $secondLevelKey): mixed
    {
        return $this->twoLevelCache[$firstLevelKey][$secondLevelKey] ?? null;
    }

    /**
     * @param string $firstLevelKey
     * @param string|int $secondLevelKey
     * @return mixed
     */
    public function get(string $firstLevelKey, string|int $secondLevelKey): mixed
    {
        if (!array_key_exists($firstLevelKey, $this->twoLevelCache)) {
            throw new FirstLevelCacheKeyNotFoundException($firstLevelKey);
        }

        if (!array_key_exists($secondLevelKey, $this->twoLevelCache[$firstLevelKey])) {
            throw new SecondLevelCacheKeyNotFoundException($firstLevelKey, $secondLevelKey);
        }

        return $this->twoLevelCache[$firstLevelKey][$secondLevelKey];
    }

    /**
     * @param string $firstLevelKey
     * @param string|int $secondLevelKey
     * @return bool
     */
    public function has(string $firstLevelKey, string|int $secondLevelKey): bool
    {
        return $this->hasFirstLevel($firstLevelKey) && array_key_exists($secondLevelKey, $this->twoLevelCache[$firstLevelKey]);
    }

    /**
     * @param string $firstLevelKey
     * @param string|int $secondLevelKey
     */
    public function delete(string $firstLevelKey, string|int $secondLevelKey): void
    {
        if (!array_key_exists($firstLevelKey, $this->twoLevelCache)) {
            throw new FirstLevelCacheKeyNotFoundException($firstLevelKey);
        }

        if (!array_key_exists($secondLevelKey, $this->twoLevelCache[$firstLevelKey])) {
            throw new SecondLevelCacheKeyNotFoundException($firstLevelKey, $secondLevelKey);
        }

        unset($this->twoLevelCache[$firstLevelKey][$secondLevelKey]);
    }

    /**
     * @param string $firstLevelKey
     * @return bool
     */
    public function hasFirstLevel(string $firstLevelKey): bool
    {
        return array_key_exists($firstLevelKey, $this->twoLevelCache);
    }

    /**
     * @param string $firstLevelKey
     * @return int
     */
    public function getItemsCount(string $firstLevelKey): int
    {
        if ($this->hasFirstLevel($firstLevelKey)) {
            return count($this->twoLevelCache[$firstLevelKey]);
        }

        throw new FirstLevelCacheKeyNotFoundException($firstLevelKey);
    }

    /**
     * @param string $firstLevelKey
     * @return array{int, mixed}
     */
    public function getItems(string $firstLevelKey): array
    {
        if ($this->hasFirstLevel($firstLevelKey)) {
            return array_values($this->twoLevelCache[$firstLevelKey]);
        }

        throw new FirstLevelCacheKeyNotFoundException($firstLevelKey);
    }

    /**
     * @param string $firstLevelKey
     * @return array
     */
    public function getSecondLevelKeys(string $firstLevelKey): array
    {
        if ($this->hasFirstLevel($firstLevelKey)) {
            return array_keys($this->twoLevelCache[$firstLevelKey]);
        }

        throw new FirstLevelCacheKeyNotFoundException($firstLevelKey);
    }

    /**
     * @param string $firstLevelKey
     */
    public function resetFistLevel(string $firstLevelKey): void
    {
        if ($this->hasFirstLevel($firstLevelKey)) {
            unset($this->twoLevelCache[$firstLevelKey]);
        }

        throw new FirstLevelCacheKeyNotFoundException($firstLevelKey);
    }

    public function reset(): void
    {
        $this->twoLevelCache = [];
    }
}
