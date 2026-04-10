<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Schema;

use RuntimeException;

class McpSchemaFileGenerator
{
    public function __construct(
        protected readonly ExposedSchemaProvider $exposedSchemaProvider,
        protected readonly string $schemaFilePath,
    ) {
    }

    public function getSchemaFilePath(): string
    {
        return $this->schemaFilePath;
    }

    public function generateSchemaFile(): bool
    {
        $generatedSchemaJson = $this->generateSchemaJson();
        $existingSchemaJson = is_file($this->schemaFilePath) ? file_get_contents($this->schemaFilePath) : false;

        if ($existingSchemaJson === $generatedSchemaJson) {
            return false;
        }

        if (@file_put_contents($this->schemaFilePath, $generatedSchemaJson) === false) {
            throw new RuntimeException(sprintf(
                'Generated MCP schema file could not be written: %s.',
                $this->schemaFilePath,
            ));
        }

        return true;
    }

    public function generateSchemaJson(): string
    {
        return $this->exposedSchemaProvider->generateExposedSchemaJson();
    }
}
