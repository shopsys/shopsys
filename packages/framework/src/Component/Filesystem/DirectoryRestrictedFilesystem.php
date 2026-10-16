<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use InvalidArgumentException;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemOperator;
use Override;
use Shopsys\FrameworkBundle\Component\Filesystem\Exception\PathOutsideAllowedDirectoryException;

/**
 * Filesystem that exposes only a single directory of the decorated filesystem.
 * Paths stay unchanged (they are not re-based), anything outside of the allowed directory is refused.
 */
class DirectoryRestrictedFilesystem implements FilesystemOperator
{
    public function __construct(
        protected readonly FilesystemOperator $filesystem,
        protected readonly string $allowedDirectory,
    ) {
        if (trim($allowedDirectory, '/') === '') {
            throw new InvalidArgumentException('The allowed directory must not be empty, it would expose the whole filesystem.');
        }
    }

    #[Override]
    public function fileExists(string $location): bool
    {
        return $this->isAllowed($location) && $this->filesystem->fileExists($location);
    }

    #[Override]
    public function directoryExists(string $location): bool
    {
        return $this->isAllowed($location) && $this->filesystem->directoryExists($location);
    }

    #[Override]
    public function has(string $location): bool
    {
        return $this->isAllowed($location) && $this->filesystem->has($location);
    }

    #[Override]
    public function read(string $location): string
    {
        $this->assertAllowed($location);

        return $this->filesystem->read($location);
    }

    #[Override]
    public function readStream(string $location)
    {
        $this->assertAllowed($location);

        return $this->filesystem->readStream($location);
    }

    #[Override]
    public function listContents(string $location, bool $deep = self::LIST_SHALLOW): DirectoryListing
    {
        $this->assertAllowed($location);

        return $this->filesystem->listContents($location, $deep);
    }

    #[Override]
    public function lastModified(string $path): int
    {
        $this->assertAllowed($path);

        return $this->filesystem->lastModified($path);
    }

    #[Override]
    public function fileSize(string $path): int
    {
        $this->assertAllowed($path);

        return $this->filesystem->fileSize($path);
    }

    #[Override]
    public function mimeType(string $path): string
    {
        $this->assertAllowed($path);

        return $this->filesystem->mimeType($path);
    }

    #[Override]
    public function visibility(string $path): string
    {
        $this->assertAllowed($path);

        return $this->filesystem->visibility($path);
    }

    /**
     * @param array<string, mixed> $config
     */
    #[Override]
    public function write(string $location, string $contents, array $config = []): void
    {
        $this->assertAllowed($location);

        $this->filesystem->write($location, $contents, $config);
    }

    /**
     * @param resource $contents
     * @param array<string, mixed> $config
     */
    #[Override]
    public function writeStream(string $location, $contents, array $config = []): void
    {
        $this->assertAllowed($location);

        $this->filesystem->writeStream($location, $contents, $config);
    }

    #[Override]
    public function setVisibility(string $path, string $visibility): void
    {
        $this->assertAllowed($path);

        $this->filesystem->setVisibility($path, $visibility);
    }

    #[Override]
    public function delete(string $location): void
    {
        $this->assertAllowed($location);

        $this->filesystem->delete($location);
    }

    #[Override]
    public function deleteDirectory(string $location): void
    {
        $this->assertAllowed($location);

        $this->filesystem->deleteDirectory($location);
    }

    /**
     * @param array<string, mixed> $config
     */
    #[Override]
    public function createDirectory(string $location, array $config = []): void
    {
        $this->assertAllowed($location);

        $this->filesystem->createDirectory($location, $config);
    }

    /**
     * @param array<string, mixed> $config
     */
    #[Override]
    public function move(string $source, string $destination, array $config = []): void
    {
        $this->assertAllowed($source);
        $this->assertAllowed($destination);

        $this->filesystem->move($source, $destination, $config);
    }

    /**
     * @param array<string, mixed> $config
     */
    #[Override]
    public function copy(string $source, string $destination, array $config = []): void
    {
        $this->assertAllowed($source);
        $this->assertAllowed($destination);

        $this->filesystem->copy($source, $destination, $config);
    }

    protected function isAllowed(string $path): bool
    {
        return PathInsideDirectoryChecker::isInside($path, $this->allowedDirectory);
    }

    protected function assertAllowed(string $path): void
    {
        if (!$this->isAllowed($path)) {
            throw new PathOutsideAllowedDirectoryException($path, $this->allowedDirectory);
        }
    }
}
