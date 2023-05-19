<?php

declare(strict_types=1);

namespace Shopsys\GoogleCloudBundle\Filesystem;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface;

class FilesystemFactoryDecorator implements FilesystemFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface $inner
     * @param string $googleCloudProjectId
     * @param string $googleCloudStorageBucketName
     */
    public function __construct(
        private readonly FilesystemFactoryInterface $inner,
        private readonly string $googleCloudProjectId,
        private readonly string $googleCloudStorageBucketName,
    ) {
    }

    /**
     * @return \League\Flysystem\FilesystemOperator
     */
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
