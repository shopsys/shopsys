<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use League\Flysystem\FilesystemException;
use League\Flysystem\WhitespacePathNormalizer;

class PathInsideDirectoryChecker
{
    /**
     * The path is normalized first (resolving "." and ".." segments), so a forged path like "web/content/wysiwyg/../../config" is correctly detected as outside.
     * Paths that cannot be normalized (traversal above the filesystem root, control characters) are never inside,
     * and nothing is inside an empty directory, so a misconfiguration fails closed.
     */
    public static function isInside(string $path, string $directory): bool
    {
        $normalizer = new WhitespacePathNormalizer();

        try {
            $normalizedPath = $normalizer->normalizePath($path);
            $normalizedDirectory = $normalizer->normalizePath($directory);
        } catch (FilesystemException) {
            return false;
        }

        if ($normalizedDirectory === '') {
            return false;
        }

        return $normalizedPath === $normalizedDirectory || str_starts_with($normalizedPath, $normalizedDirectory . '/');
    }
}
