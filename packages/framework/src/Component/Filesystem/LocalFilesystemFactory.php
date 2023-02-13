<?php

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;

class LocalFilesystemFactory implements FilesystemFactoryInterface
{
    /**
     * @return \League\Flysystem\FilesystemOperator
     */
    public function create(): FilesystemOperator
    {
        $adapter = new LocalFilesystemAdapter('/');

        return new Filesystem($adapter);
    }
}
