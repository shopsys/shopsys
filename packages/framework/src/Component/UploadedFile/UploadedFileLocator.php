<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Override;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileLocator;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;

class UploadedFileLocator extends AbstractUploadedFileLocator
{
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

    #[Override]
    protected function getFilePath(UploadedFileInterface $uploadedFile): string
    {
        return $uploadedFile->getFilename();
    }
}
