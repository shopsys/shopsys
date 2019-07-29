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
     */
    public function uploadFile($entity, $temporaryFilenames)
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

            $newUploadedFile = $this->uploadedFileFactory->create(
                $uploadedFileEntityConfig->getEntityName(),
                $entityId,
                $temporaryFilenames
            );
            $this->em->persist($newUploadedFile);
            $entitiesForFlush[] = $newUploadedFile;

            $this->em->flush($entitiesForFlush);
        }
    }

    /**
     * @param object $entity
     */
    public function deleteUploadedFileByEntity($entity)
    {
        $uploadedFile = $this->getUploadedFileByEntity($entity);
        $this->em->remove($uploadedFile);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     */
    public function deleteFileFromFilesystem(UploadedFile $uploadedFile)
    {
        $filepath = $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFile);

        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
        }
    }

    /**
     * @param object $entity
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function getUploadedFileByEntity($entity)
    {
        return $this->uploadedFileRepository->getUploadedFileByEntity(
            $this->uploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity)
        );
    }

    /**
     * @param object $entity
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile|null
     */
    public function findUploadedFileByEntity($entity)
    {
        return $this->uploadedFileRepository->findUploadedFileByEntity(
            $this->uploadedFileConfig->getEntityName($entity),
            $this->getEntityId($entity)
        );
    }

    /**
     * @param object $entity
     * @return int
     */
    protected function getEntityId($entity)
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
    public function getById($uploadedFileId)
    {
        return $this->uploadedFileRepository->getById($uploadedFileId);
    }

    /**
     * @param Object $entity
     * @return bool
     */
    public function hasUploadedFile($entity)
    {
        try {
            $uploadedFile = $this->getUploadedFileByEntity($entity);
        } catch (\Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException $e) {
            return false;
        }

        return $this->uploadedFileLocator->fileExists($uploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(UploadedFile $uploadedFile)
    {
        return $this->uploadedFileLocator->getAbsoluteUploadedFileFilepath($uploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $uploadedFile
     * @return string
     */
    public function getUploadedFileUrl(DomainConfig $domainConfig, UploadedFile $uploadedFile)
    {
        return $this->uploadedFileLocator->getUploadedFileUrl($domainConfig, $uploadedFile);
    }
}
