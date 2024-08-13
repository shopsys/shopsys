<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileLocator;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;

class UploadedFileLocator extends AbstractUploadedFileLocator
{
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
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface $uploadedFile
     * @return string
     */
    protected function getFilePath(UploadedFileInterface $uploadedFile): string
    {
        return $uploadedFile->getFilename();
    }
}
