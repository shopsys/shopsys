<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use League\Flysystem\FilesystemOperator;

interface FilesystemFactoryInterface
{
    public function create(): FilesystemOperator;
}
