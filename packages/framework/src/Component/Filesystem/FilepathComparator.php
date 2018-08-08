<?php

namespace Shopsys\FrameworkBundle\Component\Filesystem;

class FilepathComparator
{
    public function isPathWithinDirectory(string $path, string $directoryPath): bool
    {
        $directoryPathRealpath = realpath($directoryPath);
        if ($directoryPathRealpath === false) {
            throw new \Shopsys\FrameworkBundle\Component\Filesystem\Exception\DirectoryDoesNotExistException(
                $directoryPath
            );
        }

        return $this->isPathWithinDirectoryRealpathRecursive($path, $directoryPathRealpath);
    }

    private function isPathWithinDirectoryRealpathRecursive(string $path, string $directoryRealpath): bool
    {
        if (realpath($path) === $directoryRealpath) {
            return true;
        }

        if ($this->hasAncestorPath($path)) {
            return $this->isPathWithinDirectoryRealpathRecursive(dirname($path), $directoryRealpath);
        } else {
            return false;
        }
    }

    private function hasAncestorPath(string $path): bool
    {
        return dirname($path) !== $path;
    }
}
