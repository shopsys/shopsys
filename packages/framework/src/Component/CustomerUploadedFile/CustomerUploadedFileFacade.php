<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileLocatorInterface;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileRepositoryInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

/**
 * @method \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile getById(int $uploadedFileId)
 * @method \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[] getUploadedFilesByEntity(object $entity, string $type = \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig::DEFAULT_TYPE_NAME)
 */
class CustomerUploadedFileFacade extends AbstractUploadedFileFacade
{
    /**
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig $customerUploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileRepository $customerUploadedFileRepository
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileLocator $customerUploadedFileLocator
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFactory $customerUploadedFileFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     */
    public function __construct(
        FilesystemOperator $filesystem,
        EntityManagerInterface $em,
        protected readonly CustomerUploadedFileConfig $customerUploadedFileConfig,
        protected readonly CustomerUploadedFileRepository $customerUploadedFileRepository,
        protected readonly CustomerUploadedFileLocator $customerUploadedFileLocator,
        protected readonly CustomerUploadedFileFactory $customerUploadedFileFactory,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
    ) {
        parent::__construct($filesystem, $em);
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
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
        ?CustomerUser $customerUser = null,
    ): void {
        $customerUploadedFileEntityConfig = $this->customerUploadedFileConfig->getUploadedFileEntityConfig($entity);
        $uploadedFileTypeConfig = $customerUploadedFileEntityConfig->getTypeByName($type);

        $uploadedFiles = $customerUploadedFileData->uploadedFiles;
        $uploadedFilenames = $customerUploadedFileData->uploadedFilenames;
        $orderedFiles = $customerUploadedFileData->orderedFiles;

        $this->updateFilesOrder($orderedFiles);
        $this->updateFilenamesAndSlugs($customerUploadedFileData->currentFilenamesIndexedById);

        if ($uploadedFileTypeConfig->isMultiple()) {
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
        if ($this->isAccessToFileDenied($hash, $customerUser)) {
            throw new InvalidArgumentException('Either hash or customerUser must be set or administrator must be logged in.');
        }

        return $this->customerUploadedFileRepository->getByIdSlugAndExtension(
            $customerUploadedFileId,
            $customerUploadedFileSlug,
            $customerUploadedFileExtension,
            $hash ? null : $customerUser,
            $hash ?? null,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileRepositoryInterface
     */
    protected function getRepository(): UploadedFileRepositoryInterface
    {
        return $this->customerUploadedFileRepository;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileLocatorInterface
     */
    protected function getFileLocator(): UploadedFileLocatorInterface
    {
        return $this->customerUploadedFileLocator;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigInterface
     */
    protected function getUploadedFileConfig(): UploadedFileConfigInterface
    {
        return $this->customerUploadedFileConfig;
    }

    /**
     * @param string|null $hash
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return bool
     */
    public function isAccessToFileDenied(?string $hash, ?CustomerUser $customerUser): bool
    {
        return !$hash && !$customerUser && !$this->administratorFrontSecurityFacade->isAdministratorLogged();
    }
}
