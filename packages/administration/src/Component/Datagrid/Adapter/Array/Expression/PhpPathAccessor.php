<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundInRowException;
use Traversable;

/**
 * Reads the values a dot notation path leads to in one row of in-memory data.
 */
final class PhpPathAccessor
{
    /**
     * A path crossing a collection leads to several values, which is how the medium matches what a query
     * matches by a subquery — a record matches when at least one of the values does.
     *
     * A path reaching through a value that is not there yields a single null, so that a missing association
     * is matched by `isNull()` the same way a stored null is. An empty collection on the way yields no value
     * at all, so that a record with no related row is never matched. A path that addresses nothing at all
     * throws, because that is a mistake in the code — a query refuses such a path as well.
     *
     * @param array<string, mixed> $row
     * @return list<mixed>
     */
    public function read(array $row, string $path): array
    {
        $values = [$row];

        foreach (explode('.', $path) as $segment) {
            $segmentValues = [];

            foreach ($values as $value) {
                $segmentValues = [...$segmentValues, ...$this->readSegment($value, $segment, $path)];
            }

            $values = $segmentValues;
        }

        return $values;
    }

    /**
     * @return list<mixed>
     */
    private function readSegment(mixed $value, string $segment, string $path): array
    {
        // the value the path reaches through is not there, so neither is anything below it
        if ($value === null) {
            return [null];
        }

        if (is_array($value) === false) {
            throw new PathNotFoundInRowException($path, $segment, []);
        }

        if (array_key_exists($segment, $value) === false) {
            throw new PathNotFoundInRowException($path, $segment, array_keys($value));
        }

        return $this->flatten($value[$segment]);
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
