<?php

declare(strict_types=1);

namespace App\Component\FileUpload;

use App\Component\Image\Image;
use App\Component\Image\ImageRepository;
use App\Component\UploadedFile\UploadedFileRepository;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\UnexpectedTypeException;
use Shopsys\FrameworkBundle\Component\FileUpload\EntityFileUploadInterface;
use Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload as BaseFileUpload;
use Shopsys\FrameworkBundle\Component\LocalCache\LocalCacheFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile as ShopsysUploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileUpload extends BaseFileUpload
{
    private const POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE = 'positionByEntityAndType';

    /**
     * @param string $temporaryDir
     * @param string $uploadedFileDir
     * @param string $imageDir
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileNamingConvention $fileNamingConvention
     * @param \League\Flysystem\MountManager $mountManager
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @param \App\Component\Image\ImageRepository $imageRepository
     * @param \App\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository
     * @param \Shopsys\FrameworkBundle\Component\LocalCache\LocalCacheFacade $localCacheFacade
     */
    public function __construct(
        $temporaryDir,
        $uploadedFileDir,
        $imageDir,
        FileNamingConvention $fileNamingConvention,
        MountManager $mountManager,
        FilesystemOperator $filesystem,
        ParameterBagInterface $parameterBag,
        private readonly ImageRepository $imageRepository,
        private readonly UploadedFileRepository $uploadedFileRepository,
        private readonly LocalCacheFacade $localCacheFacade,
    ) {
        parent::__construct(
            $temporaryDir,
            $uploadedFileDir,
            $imageDir,
            $fileNamingConvention,
            $mountManager,
            $filesystem,
            $parameterBag,
        );
    }

    /**
     * @param \App\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     */
    public function preFlushEntity(EntityFileUploadInterface $entity)
    {
        parent::preFlushEntity($entity);

        if ($entity->getPosition() === null) {
            $entity->setPosition($this->getPositionForNewEntity($entity));
        }
    }

    /**
     * @param \App\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     * @return int
     */
    private function getPositionForNewEntity(EntityFileUploadInterface $entity): int
    {
        $entityName = $entity->getEntityName();
        $entityId = $entity->getEntityId();
        $type = $entity->getType();
        $uploadEntityType = $this->getUploadEntityType($entity);
        $key = sprintf('%s~%s~%s~%s', $entityName, $entityId, $type, $uploadEntityType);

        if ($this->localCacheFacade->hasItem(self::POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE, $key)) {
            $position = $this->localCacheFacade->getItem(self::POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE, $key);
            $position++;

            $this->localCacheFacade->save(self::POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE, $key, $position);

            return $position;
        }

        if ($uploadEntityType === 'image') {
            $position = $this->imageRepository->getImagesCountByEntityIndexedById(
                $entityName,
                $entityId,
                $type,
            );
        } else {
            $position = $this->uploadedFileRepository->getUploadedFilesCountByEntityIndexedById(
                $entityName,
                $entityId,
                $type,
            );
        }

        $this->localCacheFacade->save(self::POSITION_BY_ENTITY_AND_TYPE_CACHE_NAMESPACE, $key, $position);

        return $position;
    }

    /**
     * @param \App\Component\Image\Image|\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile $entity
     * @return string
     */
    private function getUploadEntityType(EntityFileUploadInterface $entity): string
    {
        $entityClass = get_class($entity);

        if ($entityClass === Image::class) {
            $uploadEntityType = 'image';
        } elseif ($entityClass === ShopsysUploadedFile::class) {
            $uploadEntityType = 'file';
        } else {
            throw new UnexpectedTypeException(
                sprintf('Provided entity with class %s was not expected.', $entityClass),
            );
        }

        return $uploadEntityType;
    }
}
