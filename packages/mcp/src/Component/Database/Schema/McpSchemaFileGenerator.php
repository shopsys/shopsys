<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Schema;

use RuntimeException;

class McpSchemaFileGenerator
{
    public function __construct(protected readonly ExposedSchemaProvider $exposedSchemaProvider)
    {
    }

    public function generateSchemaFile(): bool
    {
        $generatedSchemaJson = $this->exposedSchemaProvider->generateExposedSchemaJson();
        $schemaFilePath = $this->exposedSchemaProvider->getSchemaFilePath();
        $existingSchemaJson = is_file($schemaFilePath) ? file_get_contents($schemaFilePath) : false;

        if ($existingSchemaJson === $generatedSchemaJson) {
            return false;
        }

        error_clear_last();

        if (file_put_contents($schemaFilePath, $generatedSchemaJson) === false) {
            $lastPhpError = error_get_last();
            $errorDetail = $lastPhpError !== null ? sprintf(' Details: %s', $lastPhpError['message']) : '';

            throw new RuntimeException(sprintf(
                'Generated MCP schema file could not be written: %s.%s',
                $schemaFilePath,
                $errorDetail,
            ));
        }

        return true;
    }
}
