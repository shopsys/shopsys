<?php

declare(strict_types=1);

namespace Shopsys\Cli\Model;

use RuntimeException;

class JsonHandler
{
    public function __construct(
        private readonly FileHandler $fileHandler,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function readJson(string $path): array
    {
        $content = $this->fileHandler->readFile($path);
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Invalid JSON in file: %s', $path));
        }

        return $decoded;
    }

    /**
     * @param array<mixed> $data
     */
    public function writeJson(string $path, array $data): void
    {
        $content = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($content === false) {
            throw new RuntimeException('Failed to encode JSON');
        }

        $this->fileHandler->writeFile($path, $content . "\n");
    }
}
