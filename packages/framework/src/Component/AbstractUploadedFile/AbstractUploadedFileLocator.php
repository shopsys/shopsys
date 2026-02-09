<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

use League\Flysystem\FilesystemOperator;
use Override;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;

abstract class AbstractUploadedFileLocator implements UploadedFileLocatorInterface
{
    public function __construct(
        protected readonly string $uploadedFileDir,
        protected readonly FilesystemOperator $filesystem,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    public function fileExists(UploadedFileInterface $uploadedFile): bool
    {
        $fileFilepath = $this->getAbsoluteUploadedFileFilepath($uploadedFile);

        return $this->filesystem->has($fileFilepath);
    }

    public function getAbsoluteFilePath(string $filePath): string
    {
        return $this->uploadedFileDir . $this->getRelativeFilePath($filePath);
    }

    public function getRelativeUploadedFileFilepath(UploadedFileInterface $uploadedFile): string
    {
        return $this->getRelativeFilePath($this->getFilePath($uploadedFile));
    }

    protected function getRelativeFilePath(string $filePath): string
    {
        return $filePath;
    }

    #[Override]
    public function getAbsoluteUploadedFileFilepath(UploadedFileInterface $uploadedFile): string
    {
        return $this->getAbsoluteFilePath($this->getFilePath($uploadedFile));
    }

    abstract protected function getFilePath(UploadedFileInterface $uploadedFile): string;
}
