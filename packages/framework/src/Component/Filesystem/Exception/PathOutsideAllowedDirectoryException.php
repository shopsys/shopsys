<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem\Exception;

use League\Flysystem\FilesystemException;
use RuntimeException;

class PathOutsideAllowedDirectoryException extends RuntimeException implements FilesystemException
{
    public function __construct(string $path, string $allowedDirectory)
    {
        parent::__construct(sprintf('Path "%s" is outside of the allowed directory "%s".', $path, $allowedDirectory));
    }
}
