<?php

declare(strict_types=1);

namespace App\Component\FileUpload;

use App\Component\Image\ImageRepository;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileRepository;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload as BaseFileUpload;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile as ShopsysUploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\ResetInterface;

class FileUpload extends BaseFileUpload implements ResetInterface
{
    /**
     * @var array<string, array<int, array<string, array<string|null, int>>>>
     */
    private array $positionByEntityAndType = [];

    /**
     * @param string $temporaryDir
     * @param array $directoriesByFileClass
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention $fileNamingConvention
     * @param \League\Flysystem\MountManager $mountManager
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \App\Component\Image\ImageRepository $imageRepository
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileRepository $customerUploadedFileRepository
     */
    public function __construct(
        $temporaryDir,
        array $directoriesByFileClass,
        FileNamingConvention $fileNamingConvention,
        MountManager $mountManager,
        FilesystemOperator $filesystem,
        ParameterBagInterface $parameterBag,
        private readonly ImageRepository $imageRepository,
        private readonly CustomerUploadedFileRepository $customerUploadedFileRepository,
    ) {
        parent::__construct(
            $temporaryDir,
            $directoriesByFileClass,
            $fileNamingConvention,
            $mountManager,
            $filesystem,
            $parameterBag,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     */
    public function preFlushEntity(EntityFileUploadInterface $entity)
    {
        parent::preFlushEntity($entity);

        if ($entity instanceof Image && $entity->getPosition() === null) {
            $entity->setPosition($this->getPositionForNewEntity($entity));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     * @return int
     */
    private function getPositionForNewEntity(EntityFileUploadInterface $entity): int
    {
        $entityName = $entity->getEntityName();
        $entityId = $entity->getEntityId();
        $type = $entity->getType();
        $uploadEntityType = $this->getUploadEntityType($entity);

        if (isset($this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type])) {
            $this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type]++;

            return $this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type];
        }

        $position = match ($uploadEntityType) {
            'image' => $this->imageRepository->getImagesCountByEntityIndexedById(
                $entityName,
                $entityId,
                $type,
            ),
            'customerFile' => $this->customerUploadedFileRepository->getCustomerUploadedFilesCountByEntityIndexedById(
                $entityName,
                $entityId,
                $type,
            ),
            default => throw new InvalidArgumentException(
                sprintf('Unknown upload entity type "%s"', $uploadEntityType),
            ),
        };
        $this->positionByEntityAndType[$entityName][$entityId][$uploadEntityType][$type] = $position;

        return $position;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile|\Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $entity
     * @return string
     */
    private function getUploadEntityType(EntityFileUploadInterface $entity): string
    {
        $entityClass = get_class($entity);

        if ($entityClass === Image::class) {
            $uploadEntityType = 'image';
        } elseif ($entityClass === ShopsysUploadedFile::class) {
            $uploadEntityType = 'file';
        } elseif ($entityClass === CustomerUploadedFile::class) {
            $uploadEntityType = 'customerFile';
        } else {
            throw new UnexpectedTypeException(
                sprintf('Provided entity with class %s was not expected.', $entityClass),
            );
        }

        return $uploadEntityType;
    }

    public function reset(): void
    {
        $this->positionByEntityAndType = [];
    }
}
