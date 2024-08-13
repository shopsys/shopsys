<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\EntityIdentifierException;

abstract class AbstractUploadedFileFacade
{
    /**
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly FilesystemOperator $filesystem,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface
     */
    public function getById(int $uploadedFileId): UploadedFileInterface
    {
        return $this->getRepository()->getById($uploadedFileId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface $uploadedFile
     * @return string
     */
    public function getAbsoluteUploadedFileFilepath(UploadedFileInterface $uploadedFile): string
    {
        return $this->getFileLocator()->getAbsoluteUploadedFileFilepath($uploadedFile);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface $uploadedFile
     */
    public function deleteFileFromFilesystem(UploadedFileInterface $uploadedFile): void
    {
        $filepath = $this->getFileLocator()->getAbsoluteUploadedFileFilepath($uploadedFile);

        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
        }
    }

    /**
     * @param object $entity
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface[]
     */
    public function getUploadedFilesByEntity(
        object $entity,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): array {
        return $this->getRepository()->getUploadedFilesByEntity(
            $this->getUploadedFileConfig()->getEntityName($entity),
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
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileRepositoryInterface
     */
    abstract protected function getRepository(): UploadedFileRepositoryInterface;

    /**
     * @return \Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileLocatorInterface
     */
    abstract protected function getFileLocator(): UploadedFileLocatorInterface;

    /**
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigInterface
     */
    abstract protected function getUploadedFileConfig(): UploadedFileConfigInterface;
}
