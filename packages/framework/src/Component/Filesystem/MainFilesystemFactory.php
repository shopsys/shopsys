<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Override;

class MainFilesystemFactory implements FilesystemFactoryInterface
{
    /**
     * @param string $projectDir
     */
    public function __construct(protected readonly string $projectDir)
    {
    }

    /**
     * @return \League\Flysystem\FilesystemOperator
     */
    #[Override]
    public function create(): FilesystemOperator
    {
        $adapter = new LocalFilesystemAdapter($this->projectDir);

        return new Filesystem($adapter);
    }
}
