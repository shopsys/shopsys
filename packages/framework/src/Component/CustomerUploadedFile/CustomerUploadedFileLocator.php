<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception\CustomerFileNotFoundException;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;

class CustomerUploadedFileLocator
{
    /**
     * @param string $customerUploadedFileDir
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly string $customerUploadedFileDir,
        protected readonly FilesystemOperator $filesystem,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(CustomerUploadedFile $customerUploadedFile): string
    {
        return $this->getAbsoluteFilePath($customerUploadedFile->getEntityName()) . '/' . $customerUploadedFile->getFilename();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile
     * @return string
     */
    public function getCustomerUploadedFileDownloadUrl(
        DomainConfig $domainConfig,
        CustomerUploadedFile $customerUploadedFile,
    ): string {
        if ($this->fileExists($customerUploadedFile)) {
            $domainRouter = $this->domainRouterFactory->getRouter($domainConfig->getId());

            return $domainRouter->generate('front_customer_uploaded_file_download', [
                'uploadedFileId' => $customerUploadedFile->getId(),
                'uploadedFilename' => $customerUploadedFile->getSlugWithExtension(),
            ]);
        }

        throw new CustomerFileNotFoundException();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile
     * @return string
     */
    public function getCustomerUploadedFileViewUrl(
        DomainConfig $domainConfig,
        CustomerUploadedFile $customerUploadedFile,
    ): string {
        if ($this->fileExists($customerUploadedFile)) {
            $domainRouter = $this->domainRouterFactory->getRouter($domainConfig->getId());

            return $domainRouter->generate('front_customer_uploaded_file_view', [
                'uploadedFileId' => $customerUploadedFile->getId(),
                'uploadedFilename' => $customerUploadedFile->getSlugWithExtension(),
            ]);
        }

        throw new CustomerFileNotFoundException();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile
     * @return bool
     */
    public function fileExists(CustomerUploadedFile $customerUploadedFile): bool
    {
        $fileFilepath = $this->getAbsoluteUploadedFileFilepath($customerUploadedFile);

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
        return $this->customerUploadedFileDir . $this->getRelativeFilePath($entityName);
    }
}
