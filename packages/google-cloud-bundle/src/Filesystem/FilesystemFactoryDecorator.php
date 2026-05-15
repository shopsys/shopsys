<?php

declare(strict_types=1);

namespace Shopsys\GoogleCloudBundle\Filesystem;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use Override;
use Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface;

class FilesystemFactoryDecorator implements FilesystemFactoryInterface
{
    public function __construct(
        protected readonly FilesystemFactoryInterface $inner,
        protected readonly string $googleCloudProjectId,
        protected readonly string $googleCloudStorageBucketName,
    ) {
    }

    #[Override]
    public function create(): FilesystemOperator
    {
        if ($this->googleCloudStorageBucketName !== '') {
            $storageClient = new StorageClient(['projectId' => $this->googleCloudProjectId]);
            $bucket = $storageClient->bucket($this->googleCloudStorageBucketName);
            $adapter = new GoogleCloudStorageAdapter($bucket);

            return new Filesystem($adapter);
        }

        return $this->inner->create();
    }
}
