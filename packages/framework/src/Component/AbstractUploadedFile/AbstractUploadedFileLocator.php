<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;

abstract class AbstractUploadedFileLocator implements UploadedFileLocatorInterface
{
    /**
     * @param string $uploadedFileDir
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly string $uploadedFileDir,
        protected readonly FilesystemOperator $filesystem,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface $uploadedFile
     * @return bool
     */
    public function fileExists(UploadedFileInterface $uploadedFile): bool
    {
        $fileFilepath = $this->getAbsoluteUploadedFileFilepath($uploadedFile);

        return $this->filesystem->has($fileFilepath);
    }

    /**
     * @param string $filePath
     * @return string
     */
    public function getAbsoluteFilePath(string $filePath): string
    {
        return $this->uploadedFileDir . $this->getRelativeFilePath($filePath);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface $uploadedFile
     * @return string
     */
    public function getRelativeUploadedFileFilepath(UploadedFileInterface $uploadedFile): string
    {
        return $this->getRelativeFilePath($this->getFilePath($uploadedFile));
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected function getRelativeFilePath(string $filePath): string
    {
        return $filePath;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface $uploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(UploadedFileInterface $uploadedFile): string
    {
        return $this->getAbsoluteFilePath($this->getFilePath($uploadedFile));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface $uploadedFile
     * @return string
     */
    abstract protected function getFilePath(UploadedFileInterface $uploadedFile): string;
}
