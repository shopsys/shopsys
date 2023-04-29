<?php

declare(strict_types=1);

namespace Shopsys\S3Bridge\Component;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface;

class FilesystemFactoryDecorator implements FilesystemFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface $inner
     * @param \Shopsys\S3Bridge\Component\S3Configuration $s3Configuration
     */
    public function __construct(
        protected readonly FilesystemFactoryInterface $inner,
        protected readonly S3Configuration $s3Configuration,
    ) {
    }

    /**
     * @return \League\Flysystem\FilesystemOperator
     */
    public function create(): FilesystemOperator
    {
        if ($this->s3Configuration->isConfigured()) {
            $s3Adapter = new AwsS3V3Adapter(
                $this->createS3Client(),
                $this->s3Configuration->bucketName
            );

            return new Filesystem($s3Adapter, ['visibility' => Visibility::PUBLIC]);
        }

        return $this->inner->create();
    }

    /**
     * @return \Aws\S3\S3Client
     */
    protected function createS3Client(): S3Client
    {
        return new S3Client([
            'version' => $this->s3Configuration->version,
            'region' => $this->s3Configuration->region,
            'endpoint' => $this->s3Configuration->endpoint,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $this->s3Configuration->accessKey,
                'secret' => $this->s3Configuration->secret,
            ],
        ]);
    }
}
