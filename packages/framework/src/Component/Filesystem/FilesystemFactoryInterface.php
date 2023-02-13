<?php

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use League\Flysystem\FilesystemOperator;

interface FilesystemFactoryInterface
{
    /**
     * @return \League\Flysystem\FilesystemOperator
     */
    public function create(): FilesystemOperator;
}
