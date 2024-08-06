<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception\EntityIdentifierException;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CustomerUploadedFileFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig $customerUploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileRepository $customerUploadedFileRepository
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileLocator $customerUploadedFileLocator
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFactory $customerUploadedFileFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CustomerUploadedFileConfig $customerUploadedFileConfig,
        protected readonly CustomerUploadedFileRepository $customerUploadedFileRepository,
        protected readonly FilesystemOperator $filesystem,
        protected readonly CustomerUploadedFileLocator $customerUploadedFileLocator,
        protected readonly CustomerUploadedFileFactory $customerUploadedFileFactory,
    ) {
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileData $customerUploadedFileData
     * @param string $type
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function manageFiles(
        object $entity,
        CustomerUploadedFileData $customerUploadedFileData,
        string $type = CustomerUploadedFileTypeConfig::DEFAULT_TYPE_NAME,
        ?CustomerUser $customerUser = null,
    ): void {
        $customerUploadedFileEntityConfig = $this->customerUploadedFileConfig->getCustomerUploadedFileEntityConfig($entity);
        $customerUploadedFileTypeConfig = $customerUploadedFileEntityConfig->getTypeByName($type);

        $uploadedFiles = $customerUploadedFileData->uploadedFiles;
        $uploadedFilenames = $customerUploadedFileData->uploadedFilenames;
        $orderedFiles = $customerUploadedFileData->orderedFiles;

        $this->updateFilesOrder($orderedFiles);
        $this->updateFilenamesAndSlugs($customerUploadedFileData->currentFilenamesIndexedById);

        if ($customerUploadedFileTypeConfig->isMultiple()) {
            $this->uploadFiles(
                $entity,
                $customerUploadedFileEntityConfig->getEntityName(),
                $type,
                $uploadedFiles,
                $uploadedFilenames,
                count($orderedFiles),
                $customerUser,
            );
        } else {
            if (count($orderedFiles) > 1) {
                array_shift($orderedFiles);
                $this->deleteFiles($entity, $orderedFiles);
            }

            $this->deleteAllUploadedFilesByEntity($entity);

            $this->uploadFile(
                $entity,
                $customerUploadedFileEntityConfig->getEntityName(),
                $type,
                array_pop($uploadedFiles),
                array_pop($uploadedFilenames),
                $customerUser,
            );
        }

        $this->deleteFiles($entity, $customerUploadedFileData->filesToDelete);
    }

    /**
     * @param object $entity
     * @param string $entityName
     * @param string $type
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    protected function uploadFile(
        object $entity,
        string $entityName,
        string $type,
        string $temporaryFilename,
        string $uploadedFilename,
        ?CustomerUser $customerUser = null,
    ): void {
        $entityId = $this->getEntityId($entity);

        $newUploadedFile = $this->customerUploadedFileFactory->create(
            $entityName,
            $entityId,
            $type,
            $temporaryFilename,
            $uploadedFilename,
            0,
            $customerUser,
        );

        $this->em->persist($newUploadedFile);
        $this->em->flush();
    }

    /**
     * @param object $entity
     * @param string $entityName
     * @param string $type
     * @param array $temporaryFilenames
     * @param array $uploadedFilenames
     * @param int $existingFilesCount
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    protected function uploadFiles(
        object $entity,
        string $entityName,
        string $type,
        array $temporaryFilenames,
        array $uploadedFilenames,
        int $existingFilesCount,
        ?CustomerUser $customerUser = null,
    ): void {
        if (count($temporaryFilenames) > 0) {
            $entityId = $this->getEntityId($entity);
            $files = $this->customerUploadedFileFactory->createMultiple(
                $entityName,
                $entityId,
                $type,
                $temporaryFilenames,
                $uploadedFilenames,
                $existingFilesCount,
                $customerUser,
            );

            foreach ($files as $file) {
                $this->em->persist($file);
            }

            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile
     */
    public function deleteFileFromFilesystem(CustomerUploadedFile $customerUploadedFile): void
    {
        $filepath = $this->customerUploadedFileLocator->getAbsoluteUploadedFileFilepath($customerUploadedFile);

        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
        }
    }

    /**
     * @param object $entity
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[] $customerUploadedFiles
     */
    public function deleteFiles(object $entity, array $customerUploadedFiles): void
    {
        $entityName = $this->customerUploadedFileConfig->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        foreach ($customerUploadedFiles as $customerUploadedFile) {
            $customerUploadedFile->checkForDelete($entityName, $entityId);
        }

        foreach ($customerUploadedFiles as $customerUploadedFile) {
            $this->em->remove($customerUploadedFile);
        }

        $this->em->flush();
    }

    /**
     * @param object $entity
     */
    public function deleteAllUploadedFilesByEntity(object $entity): void
    {
        $customerUploadedFiles = $this->customerUploadedFileRepository->getAllCustomerUploadedFilesByEntity(
            $this->customerUploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity),
        );

        $this->deleteFiles($entity, $customerUploadedFiles);
    }

    /**
     * @param object $entity
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[]
     */
    public function getUploadedFilesByEntity(
        object $entity,
        string $type = CustomerUploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): array {
        return $this->customerUploadedFileRepository->getCustomerUploadedFilesByEntity(
            $this->customerUploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type,
        );
    }

    /**
     * @param object $entity
     * @return int
     */
    protected function getEntityId(object $entity): int
    {
        $entityMetadata = $this->em->getClassMetadata(get_class($entity));
        $identifier = $entityMetadata->getIdentifierValues($entity);

        if (count($identifier) === 1) {
            return array_pop($identifier);
        }

        $message = 'Entity "' . get_class($entity) . '" has not set primary key or primary key is compound."';

        throw new EntityIdentifierException($message);
    }

    /**
     * @param int $customerUploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile
     */
    public function getById(int $customerUploadedFileId): CustomerUploadedFile
    {
        return $this->customerUploadedFileRepository->getById($customerUploadedFileId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(CustomerUploadedFile $customerUploadedFile): string
    {
        return $this->customerUploadedFileLocator->getAbsoluteUploadedFileFilepath($customerUploadedFile);
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
        return $this->customerUploadedFileLocator->getCustomerUploadedFileDownloadUrl($domainConfig, $customerUploadedFile);
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
        return $this->customerUploadedFileLocator->getCustomerUploadedFileViewUrl($domainConfig, $customerUploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[] $customerUploadedFiles
     */
    protected function updateFilesOrder(array $customerUploadedFiles): void
    {
        $i = 0;

        foreach ($customerUploadedFiles as $customerUploadedFile) {
            $customerUploadedFile->setPosition($i++);
        }

        $this->em->flush();
    }

    /**
     * @param array $fileNamesIndexedByFileId
     */
    protected function updateFilenamesAndSlugs(array $fileNamesIndexedByFileId): void
    {
        foreach ($fileNamesIndexedByFileId as $fileId => $fileName) {
            $file = $this->getById($fileId);

            $file->setNameAndSlug($fileName);

            $this->em->flush();
        }
    }

    /**
     * @param int $customerUploadedFileId
     * @param string $customerUploadedFileSlug
     * @param string $customerUploadedFileExtension
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $hash
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile
     */
    public function getByIdSlugExtensionAndCustomerUserOrHash(
        int $customerUploadedFileId,
        string $customerUploadedFileSlug,
        string $customerUploadedFileExtension,
        ?CustomerUser $customerUser = null,
        ?string $hash = null,
    ): CustomerUploadedFile {
        if (!$hash && !$customerUser) {
            throw new InvalidArgumentException('Either hash or customerUser must be set.');
        }

        return $this->customerUploadedFileRepository->getByIdSlugAndExtension(
            $customerUploadedFileId,
            $customerUploadedFileSlug,
            $customerUploadedFileExtension,
            $hash ? null : $customerUser,
            $hash ?? null,
        );
    }
}
