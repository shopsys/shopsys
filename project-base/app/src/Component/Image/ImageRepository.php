<?php

declare(strict_types=1);

namespace App\Component\Image;

use Doctrine\ORM\PersistentCollection;
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
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityIndexedById($entityName, $entityId, $type): array
    {
        /** @var \Shopsys\FrameworkBundle\Component\Image\Image[] $images */
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

    /**
     * @param $entityName
     * @param $entityId
     * @param $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
    public function getImageByEntity($entityName, $entityId, $type): Image
    {
        /** @var \Shopsys\FrameworkBundle\Component\Image\Image $image */
        $image = parent::getImageByEntity($entityName, $entityId, $type);
        /** @var \Doctrine\ORM\PersistentCollection $translations */
        $translations = $image->getTranslations();

        if ($translations instanceof PersistentCollection) {
            $translations->initialize();
        }

        return $image;
    }

    /**
     * @param int $imageId
     * @return \Shopsys\FrameworkBundle\Component\Image\Image|null
     */
    public function findById(int $imageId): ?Image
    {
        return $this->getImageRepository()->find($imageId);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return int
     */
    public function getImagesCountByEntityIndexedById(string $entityName, int $entityId, ?string $type = null): int
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('COUNT(i)')
            ->from(Image::class, 'i', 'i.id')
            ->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.entityId = :entityId')->setParameter('entityId', $entityId);

        if ($type === null) {
            $queryBuilder->andWhere('i.type IS NULL');
        } else {
            $queryBuilder->andWhere('i.type = :type')->setParameter('type', $type);
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
