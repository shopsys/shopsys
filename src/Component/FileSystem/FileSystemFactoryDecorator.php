<?php

declare(strict_types=1);

namespace App\Component\FileSystem;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface;

class FileSystemFactoryDecorator implements FilesystemFactoryInterface
{
    /**
     * @var string
     */
    protected $s3ApiHost;

    /**
     * @var string
     */
    protected $s3ApiUsername;

    /**
     * @var string
     */
    protected $s3ApiPassword;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface
     */
    protected $inner;

    /**
     * @var string
     */
    protected $s3ApiBucketName;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Filesystem\FilesystemFactoryInterface $inner
     * @param string $s3ApiHost
     * @param string $s3ApiUsername
     * @param string $s3ApiPassword
     * @param string $s3ApiBucketName
     */
    public function __construct(
        FilesystemFactoryInterface $inner,
        string $s3ApiHost,
        string $s3ApiUsername,
        string $s3ApiPassword,
        string $s3ApiBucketName
    ) {
        $this->inner = $inner;
        $this->s3ApiHost = $s3ApiHost;
        $this->s3ApiUsername = $s3ApiUsername;
        $this->s3ApiPassword = $s3ApiPassword;
        $this->s3ApiBucketName = $s3ApiBucketName;
    }

    /**
     * @return \League\Flysystem\FilesystemInterface
     */
    public function create(): FilesystemInterface
    {
        if ($this->s3ApiHost != '') {
            $s3Client = new S3Client([
                'version' => '2006-03-01',
                'region' => '',
                'endpoint' => $this->s3ApiHost,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $this->s3ApiUsername,
                    'secret' => $this->s3ApiPassword,
                ],
            ]);

            $s3Adapter = new AwsS3Adapter($s3Client, $this->s3ApiBucketName);
            $cachedAdapter = new CachedAdapter($s3Adapter, new Memory());
            return new Filesystem($cachedAdapter, [
                'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
            ]);
        }

        return $this->inner->create();
    }
}
