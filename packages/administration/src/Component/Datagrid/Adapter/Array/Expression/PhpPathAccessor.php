<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Traversable;

final class PhpPathAccessor
{
    /**
     * Returns every value the path leads to:
     *
     * - a path crossing a collection returns a value for each related row — `items.name` of an order with
     *   two items returns two names;
     * - a path reaching through a missing value returns a single null — `customerUser.email` of an order
     *   without a customer;
     * - a path crossing an empty collection returns no value at all;
     * - a value stored under the whole dot notation key (`customerUser.email`) is read before the nested arrays;
     * - a path addressing nothing in the row is a mistake in the code and fails.
     *
     * @param array<string, mixed> $row
     * @return list<mixed>
     */
    public function read(array $row, string $path): array
    {
        return $this->readSegments($row, explode('.', $path), $path);
    }

    /**
     * @param non-empty-list<string> $segments
     * @return list<mixed>
     */
    private function readSegments(mixed $value, array $segments, string $path): array
    {
        if ($value === null) {
            return [null];
        }

        if (is_array($value) === false) {
            throw UnsupportedConditionException::pathNotFoundInRow($path, $segments[0], []);
        }

        $remainingPath = implode('.', $segments);

        if (count($segments) > 1 && array_key_exists($remainingPath, $value)) {
            return $this->flatten($value[$remainingPath]);
        }

        $segment = array_shift($segments);

        if (array_key_exists($segment, $value) === false) {
            throw UnsupportedConditionException::pathNotFoundInRow($path, $segment, array_keys($value));
        }

        if ($segments === []) {
            return $this->flatten($value[$segment]);
        }

        $values = [];

        foreach ($this->flatten($value[$segment]) as $segmentValue) {
            $values = [...$values, ...$this->readSegments($segmentValue, $segments, $path)];
        }

        return $values;
    }

    /**
     * @return list<mixed>
     */
    private function flatten(mixed $value): array
    {
        if ($value instanceof Traversable) {
            return array_values(iterator_to_array($value));
        }

        if (is_array($value) && array_is_list($value)) {
            return $value;
        }

        return [$value];
    }
}
