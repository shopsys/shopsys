<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Tests;

/**
 * @phpstan-type FieldOptions array {
 *     label?: string,
 *     visible?: bool,
 *     help?: string|null,
 *     template?: string|null,
 *     transform?: null|callable(mixed $value, mixed[] $row, mixed[][] $results): mixed,
 *     property?: string|null
 * }
 */
class SomeClass
{
    /**
     * Define a new field in datagrid
     *
     * @param string $name
     * @param array{
     *       label?: string,
     *       visible?: bool,
     *       help?: string|null,
     *       template?: string|null,
     *       transform?: null|callable(mixed $value, mixed[] $row, mixed[][] $results): mixed,
     *       property?: string|null
     *   } $options
     * @phpstan-param FieldOptions $options
     * @return self
     */
    public function add(string $name, array $options = []): self
    {
        return $this;
    }


    /**
     * Update options of field in datagrid
     *
     * @param array{
     *       label?: string,
     *       visible?: bool,
     *       help?: string|null,
     *       template?: string|null,
     *       transform?: null|callable(mixed $value, mixed[] $row, mixed[][] $results): mixed,
     *       property?: string|null
     *   } $options
     * @phpstan-param FieldOptions $options
     */
    public function edit(string $name, array $options = []): self
    {
        return $this;
    }
}
