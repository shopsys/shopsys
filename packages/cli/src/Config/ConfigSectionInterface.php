<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config;

interface ConfigSectionInterface
{
    /**
     * Unique key for YAML mapping (e.g., 'map_settings')
     */
    public static function getKey(): string;

    /**
     * Populate from parsed YAML/array data
     *
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data): void;

    /**
     * Validate current values, throw on error
     */
    public function validate(): void;

    /**
     * Serialize to array for YAML output
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
