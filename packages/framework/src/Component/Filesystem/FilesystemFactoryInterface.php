<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use League\Flysystem\FilesystemOperator;

interface FilesystemFactoryInterface
{
    /**
     * @param string $rootDirectory directory that becomes the root of the filesystem, nothing outside of it is accessible
     */
    public function create(string $rootDirectory = ''): FilesystemOperator;
}
