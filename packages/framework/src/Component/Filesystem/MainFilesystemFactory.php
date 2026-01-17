<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Override;

class MainFilesystemFactory implements FilesystemFactoryInterface
{
    public function __construct(protected readonly string $projectDir)
    {
    }

    #[Override]
    public function create(): FilesystemOperator
    {
        $adapter = new LocalFilesystemAdapter($this->projectDir);

        return new Filesystem($adapter);
    }
}
