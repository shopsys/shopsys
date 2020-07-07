<?php

declare(strict_types=1);

namespace App\Component\Image\Processing;

use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor as BaseImageProcessor;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\Filesystem\Filesystem;

class ImageProcessor extends BaseImageProcessor
{
    public const LOCAL_TEMPORARY_DIRECTORY = 'imageProcessor';

    /**
     * @var \League\Flysystem\MountManager
     */
    private $mountManager;

    /**
     * @var string
     */
    private $localTemporaryDir;

    /**
     * @param \Intervention\Image\ImageManager $imageManager
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param string $localTemporaryDir
     */
    public function __construct(
        ImageManager $imageManager,
        FilesystemInterface $filesystem,
        Filesystem $localFilesystem,
        MountManager $mountManager,
        string $localTemporaryDir
    ) {
        parent::__construct($imageManager, $filesystem, $localFilesystem);
        $this->mountManager = $mountManager;
        $this->localTemporaryDir = $localTemporaryDir;
    }

    /**
     * @inheritDoc
     */
    public function convertToShopFormatAndGetNewFilename($filepath)
    {
        $filepathForProcessing = $filepath;
        $isOnMainFilesystem = $this->filesystem->has($filepath);
        $originalPathinfo = null;

        // Temporary copying of file to local filesystem
        if ($isOnMainFilesystem === true) {
            $originalPathinfo = pathinfo($filepath);
            $filepathForProcessing = $this->getLocalTemporaryDirectory() . '/'
                . uniqid($originalPathinfo['filename'] . '_', true) . '.' . $originalPathinfo['extension'];
            $this->mountManager->copy(
                'main://' . $filepath,
                'local://' . TransformString::removeDriveLetterFromPath($filepathForProcessing)
            );
        }

        $newFilename = parent::convertToShopFormatAndGetNewFilename($filepathForProcessing);

        // move file back to main storage after processing
        if ($isOnMainFilesystem === true) {
            $this->mountManager->move(
                'local://' . TransformString::removeDriveLetterFromPath($this->getLocalTemporaryDirectory() . '/' . $newFilename),
                'main://' . $originalPathinfo['dirname'] . '/' . $newFilename
            );

            if ($newFilename !== $originalPathinfo['basename']) {
                $this->filesystem->delete($filepath);
            }
        }

        return $newFilename;
    }

    /**
     * @return string
     */
    private function getLocalTemporaryDirectory(): string
    {
        return $this->localTemporaryDir . '/' . static::LOCAL_TEMPORARY_DIRECTORY;
    }
}
