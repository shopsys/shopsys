<?php

declare(strict_types=1);

namespace Shopsys\Cli\Model;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

final class FileHandler
{
    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
    }

    public function readFile(string $path): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException(sprintf('File not found: %s', $path));
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException(sprintf('Could not read file: %s', $path));
        }

        return $content;
    }

    public function writeFile(string $path, string $content): void
    {
        $this->ensureDirectory(dirname($path));
        $this->filesystem->dumpFile($path, $content);
    }

    public function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            $this->filesystem->mkdir($path, 0755);
        }
    }

    public function copyFile(string $source, string $destination): void
    {
        $this->ensureDirectory(dirname($destination));
        $this->filesystem->copy($source, $destination, true);
    }

    public function deleteFile(string $path): void
    {
        if (file_exists($path)) {
            $this->filesystem->remove($path);
        }
    }
}
