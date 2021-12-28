<?php

declare(strict_types=1);

namespace App\Component\Image;

use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository as BaseImageRepository;

/**
 * @method \App\Component\Image\Image|null findImageByEntity(string $entityName, int $entityId, string|null $type)
 * @method \App\Component\Image\Image getImageByEntity(string $entityName, int $entityId, string|null $type)
 * @method \App\Component\Image\Image[] getImagesByEntityIndexedById(string $entityName, int $entityId, string|null $type)
 * @method \App\Component\Image\Image[] getAllImagesByEntity(string $entityName, int $entityId)
 * @method \App\Component\Image\Image getById(int $imageId)
 * @method \App\Component\Image\Image[] getMainImagesByEntitiesIndexedByEntityId(array $entitiesOrEntityIds, string $entityName)
 * @method \App\Component\Image\Image[] getMainImagesByEntitiesIndexedByEntityId(array $entities, string $entityName)
 */
class ImageRepository extends BaseImageRepository
{
    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $akeneoImageType
     * @return \App\Component\Image\Image|null
     */
    public function findImageByEntityForAkeneoImageType(string $entityName, int $entityId, string $akeneoImageType): ?Image
    {
        return $this->getImageRepository()->findOneBy(
            [
                'entityName' => $entityName,
                'entityId' => $entityId,
                'akeneoImageType' => $akeneoImageType,
            ],
            [
                'position' => 'asc',
                'id' => 'asc',
            ]
        );
    }

    /**
     * @param int $imageId
     * @return \App\Component\Image\Image|null
     */
    public function findById($imageId): ?Image
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
