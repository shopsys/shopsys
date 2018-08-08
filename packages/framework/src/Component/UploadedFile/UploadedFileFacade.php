<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;

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
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileService
     */
    protected $uploadedFileService;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator
     */
    protected $uploadedFileLocator;

    public function __construct(
        EntityManagerInterface $em,
        UploadedFileConfig $uploadedFileConfig,
        UploadedFileRepository $uploadedFileRepository,
        UploadedFileService $uploadedFileService,
        FilesystemInterface $filesystem,
        UploadedFileLocator $uploadedFileLocator
    ) {
        $this->em = $em;
        $this->uploadedFileConfig = $uploadedFileConfig;
        $this->uploadedFileRepository = $uploadedFileRepository;
        $this->uploadedFileService = $uploadedFileService;
        $this->filesystem = $filesystem;
        $this->uploadedFileLocator = $uploadedFileLocator;
    }

    /**
     * @param array|null $temporaryFilenames
     */
    public function uploadFile(object $entity, ?array $temporaryFilenames): void
    {
        if ($temporaryFilenames !== null && count($temporaryFilenames) > 0) {
            $entitiesForFlush = [];
            $uploadedFileEntityConfig = $this->uploadedFileConfig->getUploadedFileEntityConfig($entity);
            $entityId = $this->getEntityId($entity);
            $oldUploadedFile = $this->uploadedFileRepository->findUploadedFileByEntity(
                $uploadedFileEntityConfig->getEntityName(),
                $entityId
            );

            if ($oldUploadedFile !== null) {
                $this->em->remove($oldUploadedFile);
                $entitiesForFlush[] = $oldUploadedFile;
            }

            $newUploadedFile = $this->uploadedFileService->createUploadedFile(
                $uploadedFileEntityConfig,
                $entityId,
                $temporaryFilenames
            );
            $this->em->persist($newUploadedFile);
            $entitiesForFlush[] = $newUploadedFile;

            $this->em->flush($entitiesForFlush);
        }
    }

    public function deleteUploadedFileByEntity(object $entity): void
    {
        $uploadedFile = $this->getUploadedFileByEntity($entity);
        $this->em->remove($uploadedFile);
        $this->em->flush();
    }

    public function deleteFileFromFilesystem(UploadedFile $uploadedFile): void
    {
        $filepath = $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFile);

        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
        }
    }

    public function getUploadedFileByEntity(object $entity): \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
    {
        return $this->uploadedFileRepository->getUploadedFileByEntity(
            $this->uploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity)
        );
    }

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

    public function getById(int $uploadedFileId): \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
    {
        return $this->uploadedFileRepository->getById($uploadedFileId);
    }

    public function hasUploadedFile(Object $entity): bool
    {
        try {
            $uploadedFile = $this->getUploadedFileByEntity($entity);
        } catch (\Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException $e) {
            return false;
        }

        return $this->uploadedFileLocator->fileExists($uploadedFile);
    }

    public function getAbsoluteUploadedFileFilepath(UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFile);
    }

    public function getUploadedFileUrl(DomainConfig $domainConfig, UploadedFile $uploadedFile): string
    {
        return $this->uploadedFileLocator->getUploadedFileUrl($domainConfig, $uploadedFile);
    }
}
