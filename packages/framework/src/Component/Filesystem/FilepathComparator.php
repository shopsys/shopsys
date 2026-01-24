<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use Shopsys\FrameworkBundle\Component\Filesystem\Exception\DirectoryDoesNotExistException;

class FilepathComparator
{
    public function isPathWithinDirectory(string $path, string $directoryPath): bool
    {
        $directoryPathRealpath = realpath($directoryPath);

        if ($directoryPathRealpath === false) {
            throw new DirectoryDoesNotExistException(
                $directoryPath,
            );
        }

        return $this->isPathWithinDirectoryRealpathRecursive($path, $directoryPathRealpath);
    }

    protected function isPathWithinDirectoryRealpathRecursive(string $path, string $directoryRealpath): bool
    {
        if (realpath($path) === $directoryRealpath) {
            return true;
        }

        if ($this->hasAncestorPath($path)) {
            return $this->isPathWithinDirectoryRealpathRecursive(dirname($path), $directoryRealpath);
        }

        return false;
    }

    protected function hasAncestorPath(string $path): bool
    {
        return dirname($path) !== $path;
    }
}
