<?php

namespace Shopsys\FrameworkBundle\Component\Filesystem;

class FilepathComparator
{
    /**
     * @param string $path
     * @param string $directoryPath
     */
    public function isPathWithinDirectory($path, $directoryPath): bool
    {
        $directoryPathRealpath = realpath($directoryPath);
        if ($directoryPathRealpath === false) {
            throw new \Shopsys\FrameworkBundle\Component\Filesystem\Exception\DirectoryDoesNotExistException(
                $directoryPath
            );
        }

        return $this->isPathWithinDirectoryRealpathRecursive($path, $directoryPathRealpath);
    }

    /**
     * @param string $path
     * @param string $directoryRealpath
     */
    private function isPathWithinDirectoryRealpathRecursive($path, $directoryRealpath): bool
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

    /**
     * @param string $path
     */
    private function hasAncestorPath($path): bool
    {
        return dirname($path) !== $path;
    }
}
