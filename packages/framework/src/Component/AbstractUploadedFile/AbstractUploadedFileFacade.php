<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AbstractUploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfigInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\EntityIdentifierException;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Mime\MimeTypesInterface;

abstract class AbstractUploadedFileFacade
{
    protected const array VIEWABLE_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    protected const string FALLBACK_MIME_TYPE = 'application/octet-stream';

    public function __construct(
        protected readonly FilesystemOperator $filesystem,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function getById(int $uploadedFileId): UploadedFileInterface
    {
        return $this->getRepository()->getById($uploadedFileId);
    }

    public function getAbsoluteUploadedFileFilepath(UploadedFileInterface $uploadedFile): string
    {
        return $this->getFileLocator()->getAbsoluteUploadedFileFilepath($uploadedFile);
    }

    public function deleteFileFromFilesystem(UploadedFileInterface $uploadedFile): void
    {
        $filepath = $this->getFileLocator()->getAbsoluteUploadedFileFilepath($uploadedFile);

        if ($this->filesystem->has($filepath)) {
            $this->filesystem->delete($filepath);
        }
    }

    /**
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

    public function isUploadedFileViewableInBrowser(AbstractUploadedFile $uploadedFile): bool
    {
        return in_array(strtolower($uploadedFile->getExtension()), static::VIEWABLE_EXTENSIONS, true);
    }

    public function getMimeTypeByExtension(AbstractUploadedFile $uploadedFile): string
    {
        $mimeTypes = $this->getMimeTypes()->getMimeTypes(strtolower($uploadedFile->getExtension()));

        return array_first($mimeTypes) ?? static::FALLBACK_MIME_TYPE;
    }

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

    protected function updateFilenamesAndSlugs(array $fileNamesIndexedByFileId): void
    {
        foreach ($fileNamesIndexedByFileId as $fileId => $fileName) {
            $file = $this->getById($fileId);
            $filename = pathinfo($fileName, PATHINFO_FILENAME);

            $file->setName($filename);

            $this->em->flush();
        }
    }

    protected function getMimeTypes(): MimeTypesInterface
    {
        return MimeTypes::getDefault();
    }

    abstract protected function getRepository(): UploadedFileRepositoryInterface;

    abstract protected function getFileLocator(): UploadedFileLocatorInterface;

    abstract protected function getUploadedFileConfig(): UploadedFileConfigInterface;
}
