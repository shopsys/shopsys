<?php

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use Shopsys\FrameworkBundle\Component\Filesystem\Exception\DirectoryDoesNotExistException;

class FilepathComparator
{
    /**
     * @param string $path
     * @param string $directoryPath
     * @return bool
     */
    public function isPathWithinDirectory(string $path, string $directoryPath): bool
    {
        $directoryPathRealpath = realpath($directoryPath);
        if ($directoryPathRealpath === false) {
            throw new DirectoryDoesNotExistException(
                $directoryPath
            );
        }

        return $this->isPathWithinDirectoryRealpathRecursive($path, $directoryPathRealpath);
    }

    /**
     * @param string $path
     * @param string $directoryRealpath
     * @return bool
     */
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

    /**
     * @param string $path
     * @return bool
     */
    protected function hasAncestorPath(string $path): bool
    {
        return dirname($path) !== $path;
    }
}
