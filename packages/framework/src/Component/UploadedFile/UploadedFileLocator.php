<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;

class UploadedFileLocator
{
    /**
     * @param string $uploadedFileDir
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly string $uploadedFileDir,
        protected readonly FilesystemOperator $filesystem,
        protected readonly DomainRouterFactory $domainRouterFactory
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getRelativeUploadedFileFilepath(UploadedFile $uploadedFile): string
    {
        return $this->getRelativeFilePath($uploadedFile->getEntityName()) . '/' . $uploadedFile->getFilename();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(UploadedFile $uploadedFile): string
    {
        return $this->getAbsoluteFilePath($uploadedFile->getEntityName()) . '/' . $uploadedFile->getFilename();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getUploadedFileUrl(DomainConfig $domainConfig, UploadedFile $uploadedFile): string
    {
        if ($this->fileExists($uploadedFile)) {
            $domainRouter = $this->domainRouterFactory->getRouter($domainConfig->getId());

            return $domainRouter->generate('front_download_uploaded_file', [
                'uploadedFileId' => $uploadedFile->getId(),
                'uploadedFilename' => $uploadedFile->getSlugWithExtension(),
            ]);
        }

        throw new FileNotFoundException();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return bool
     */
    public function fileExists(UploadedFile $uploadedFile): bool
    {
        $fileFilepath = $this->getAbsoluteUploadedFileFilepath($uploadedFile);

        return $this->filesystem->has($fileFilepath);
    }

    /**
     * @param string $entityName
     * @return string
     */
    protected function getRelativeFilePath(string $entityName): string
    {
        return $entityName;
    }

    /**
     * @param string $entityName
     * @return string
     */
    public function getAbsoluteFilePath(string $entityName): string
    {
        return $this->uploadedFileDir . $this->getRelativeFilePath($entityName);
    }
}
