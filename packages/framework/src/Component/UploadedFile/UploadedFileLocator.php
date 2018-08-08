<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class UploadedFileLocator
{
    /**
     * @var string
     */
    private $uploadedFileDir;

    /**
     * @var string
     */
    private $uploadedFileUrlPrefix;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @param string $uploadedFileDir
     * @param string $uploadedFileUrlPrefix
     */
    public function __construct($uploadedFileDir, $uploadedFileUrlPrefix, FilesystemInterface $filesystem)
    {
        $this->uploadedFileDir = $uploadedFileDir;
        $this->uploadedFileUrlPrefix = $uploadedFileUrlPrefix;
        $this->filesystem = $filesystem;
    }

    public function getRelativeUploadedFileFilepath(UploadedFile $uploadedFile): string
    {
        return $this->getRelativeFilePath($uploadedFile->getEntityName()) . '/' . $uploadedFile->getFilename();
    }

    public function getAbsoluteUploadedFileFilepath(UploadedFile $uploadedFile): string
    {
        return $this->getAbsoluteFilePath($uploadedFile->getEntityName()) . '/' . $uploadedFile->getFilename();
    }

    public function getUploadedFileUrl(DomainConfig $domainConfig, UploadedFile $uploadedFile): string
    {
        if ($this->fileExists($uploadedFile)) {
            return $domainConfig->getUrl()
            . $this->uploadedFileUrlPrefix
            . $this->getRelativeUploadedFileFilepath($uploadedFile);
        }

        throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException();
    }

    public function fileExists(UploadedFile $uploadedFile): bool
    {
        $fileFilepath = $this->getAbsoluteUploadedFileFilepath($uploadedFile);

        return $this->filesystem->has($fileFilepath);
    }

    /**
     * @param string $entityName
     */
    private function getRelativeFilePath($entityName): string
    {
        return $entityName;
    }

    /**
     * @param string $entityName
     */
    public function getAbsoluteFilePath($entityName): string
    {
        return $this->uploadedFileDir . $this->getRelativeFilePath($entityName);
    }
}
