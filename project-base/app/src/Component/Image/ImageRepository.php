<?php

declare(strict_types=1);

namespace App\Component\Image;

use Doctrine\ORM\PersistentCollection;
use Override;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository as BaseImageRepository;

/**
 * @method \Shopsys\FrameworkBundle\Component\Image\Image|null findImageByEntity(string $entityName, int $entityId, string|null $type)
 * @method \Shopsys\FrameworkBundle\Component\Image\Image[] getAllImagesByEntity(string $entityName, int $entityId)
 * @method \Shopsys\FrameworkBundle\Component\Image\Image getById(int $imageId)
 * @method \Shopsys\FrameworkBundle\Component\Image\Image[] getMainImagesByEntitiesIndexedByEntityId(array $entitiesOrEntityIds, string $entityName)
 */
class ImageRepository extends BaseImageRepository
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getImagesByEntityIndexedById(string $entityName, int $entityId, ?string $type): array
    {
        $images = parent::getImagesByEntityIndexedById(
            $entityName,
            $entityId,
            $type,
        );

        foreach ($images as &$image) {
            /** @var \Doctrine\ORM\PersistentCollection $translations */
            $translations = $image->getTranslations();

            if ($translations instanceof PersistentCollection) {
                $translations->initialize();
            }
        }

        return $images;
    }

    #[Override]
    public function getImageByEntity(string $entityName, int $entityId, ?string $type): Image
    {
        $image = parent::getImageByEntity($entityName, $entityId, $type);
        /** @var \Doctrine\ORM\PersistentCollection $translations */
        $translations = $image->getTranslations();

        if ($translations instanceof PersistentCollection) {
            $translations->initialize();
        }

        return $image;
    }

    public function findById(int $imageId): ?Image
    {
        return $this->getImageRepository()->find($imageId);
    }
}
