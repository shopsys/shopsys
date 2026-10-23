<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Override;

class LocalFilesystemFactory implements FilesystemFactoryInterface
{
    #[Override]
    public function create(string $rootDirectory = ''): FilesystemOperator
    {
        $adapter = new LocalFilesystemAdapter('/' . ltrim($rootDirectory, '/'));

        return new Filesystem($adapter);
    }
}
