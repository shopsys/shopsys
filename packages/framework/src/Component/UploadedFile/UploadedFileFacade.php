<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

class UploadedFileFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig
     */
    protected $uploadedFileConfig;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository
     */
    protected $uploadedFileRepository;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator
     */
    protected $uploadedFileLocator;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactoryInterface
     */
    protected $uploadedFileFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator $uploadedFileLocator
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactoryInterface $uploadedFileFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        UploadedFileConfig $uploadedFileConfig,
        UploadedFileRepository $uploadedFileRepository,
        FilesystemInterface $filesystem,
        UploadedFileLocator $uploadedFileLocator,
        UploadedFileFactoryInterface $uploadedFileFactory
    ) {
        $this->em = $em;
        $this->uploadedFileConfig = $uploadedFileConfig;
        $this->uploadedFileRepository = $uploadedFileRepository;
        $this->filesystem = $filesystem;
        $this->uploadedFileLocator = $uploadedFileLocator;
        $this->uploadedFileFactory = $uploadedFileFactory;
    }

    /**
     * @param object $entity
     * @param array|null $temporaryFilenames
     * @param string $type
     */
    public function uploadFile(object $entity, ?array $temporaryFilenames, string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME): void
    {
        if ($temporaryFilenames !== null && count($temporaryFilenames) > 0) {
            $uploadedFileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfig($entity);

            # will be used in next commit - now just checks existence of such type
            $uploadedFileTypeConfig = $uploadedFileEntityConfig->getTypeByName($type);

            $entityId = $this->getEntityId($entity);

            $this->deleteAllUploadedFilesByEntity($entity);

            $newUploadedFile = $this->uploadedFileFactory->create(
                $uploadedFileEntityConfig->getEntityName(),
                $entityId,
                $type,
                $temporaryFilenames
            );

            $this->em->persist($newUploadedFile);
            $this->em->flush($newUploadedFile);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     */
    public function deleteFileFromFilesystem(UploadedFile $uploadedFile): void
    {
        $filepath = $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFile);

        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
        }
    }

    /**
     * @param object $entity
     */
    public function deleteAllUploadedFilesByEntity(object $entity): void
    {
        $uploadedFiles = $this->uploadedFileRepository->getAllUploadedFilesByEntity(
            $this->uploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity)
        );

        foreach ($uploadedFiles as $uploadedFile) {
            $this->em->remove($uploadedFile);
        }

        $this->em->flush($uploadedFiles);
    }

    /**
     * @param object $entity
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function getUploadedFilesByEntity(object $entity, string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME): array
    {
        return $this->uploadedFileRepository->getUploadedFilesByEntity(
            $this->uploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity),
            $type
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
        throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Exception\EntityIdentifierException($message);
    }

    /**
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function getById(int $uploadedFileId): UploadedFile
    {
        return $this->uploadedFileRepository->getById($uploadedFileId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getUploadedFileUrl(DomainConfig $domainConfig, UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileLocator->getUploadedFileUrl($domainConfig, $uploadedFile);
    }
}
