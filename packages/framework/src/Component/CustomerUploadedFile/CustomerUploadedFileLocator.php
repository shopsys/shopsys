<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileLocator;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception\CustomerFileNotFoundException;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class CustomerUploadedFileLocator extends AbstractUploadedFileLocator
{
    public function getCustomerUploadedFileDownloadUrl(
        DomainConfig $domainConfig,
        CustomerUploadedFile $customerUploadedFile,
    ): string {
        if ($this->fileExists($customerUploadedFile)) {
            $domainRouter = $this->domainRouterFactory->getRouter($domainConfig->getId());

            return $domainRouter->generate('front_customer_uploaded_file_download', [
                'uploadedFileId' => $customerUploadedFile->getId(),
                'uploadedFilename' => $customerUploadedFile->getSlugWithExtension(),
                'hash' => $customerUploadedFile->getHash(),
            ]);
        }

        throw new CustomerFileNotFoundException();
    }

    public function getCustomerUploadedFileViewUrl(
        DomainConfig $domainConfig,
        CustomerUploadedFile $customerUploadedFile,
    ): string {
        if ($this->fileExists($customerUploadedFile)) {
            $domainRouter = $this->domainRouterFactory->getRouter($domainConfig->getId());

            return $domainRouter->generate('front_customer_uploaded_file_view', [
                'uploadedFileId' => $customerUploadedFile->getId(),
                'uploadedFilename' => $customerUploadedFile->getSlugWithExtension(),
                'hash' => $customerUploadedFile->getHash(),
            ]);
        }

        throw new CustomerFileNotFoundException();
    }

    #[Override]
    protected function getFilePath(UploadedFileInterface $uploadedFile): string
    {
        if (!$uploadedFile instanceof CustomerUploadedFile) {
            throw new InvalidArgumentException(
                sprintf('Instance of CustomerUploadedFile expected, got %s', get_class($uploadedFile)),
            );
        }

        return sprintf('%s/%s', $uploadedFile->getEntityName(), $uploadedFile->getFilename());
    }
}
