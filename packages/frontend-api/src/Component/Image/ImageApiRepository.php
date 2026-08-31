<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Image;

class ImageApiRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param int[] $entityIds
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]|null[]
     */
    public function getImagesByTypeAndPositionIndexedByEntityId(
        array $entityIds,
        string $entityName,
        ?string $type,
    ): array {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata($this->entityNameResolver->resolve(Image::class), 'i');

        $queryBuilder = $this->entityManager->createNativeQuery(
            '
        SELECT i.* FROM images as i
        JOIN (
            SELECT entity_name, entity_id, COALESCE(type, \'\') as type, MIN(COALESCE(position, 0)  )
            FROM images 
            WHERE entity_name = :entityName 
              AND entity_id IN (:entities)
              AND COALESCE(type, \'\') = :type
            GROUP BY entity_name, entity_id, type
        ) as isub 
        ON i.entity_name = isub.entity_name 
            AND i.entity_id = isub.entity_id
            AND COALESCE(i.type, \'\') = isub.type
            AND COALESCE(i.position, 0) = isub.min',
            $rsm,
        );

        $queryBuilder->setParameter('entityName', $entityName);
        $queryBuilder->setParameter('entities', $entityIds);
        $queryBuilder->setParameter('type', $type ?? '');

        $imagesByEntityId = array_fill_keys($entityIds, null);

        /** @var \Shopsys\FrameworkBundle\Component\Image\Image $image */
        foreach ($queryBuilder->getResult() as $image) {
            $imagesByEntityId[$image->getEntityId()] = $image;
        }

        return $imagesByEntityId;
    }

    /**
     * @param int[] $entityIds
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[][]
     */
    public function getAllImagesIndexedByEntityId(array $entityIds, string $entityName, ?string $type): array
    {
        $imagesByEntityId = array_fill_keys($entityIds, []);
        $queryBuilder = $this->entityManager->getRepository(Image::class)
            ->createQueryBuilder('i')
            ->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.entityId IN (:entities)')->setParameter('entities', $entityIds)
            ->addOrderBy('i.position', 'asc')
            ->addOrderBy('i.id', 'asc');

        if ($type === null) {
            $queryBuilder->andWhere('i.type IS NULL');
        } else {
            $queryBuilder->andWhere('i.type = :type')->setParameter('type', $type);
        }

        /** @var \Shopsys\FrameworkBundle\Component\Image\Image $image */
        foreach ($queryBuilder->getQuery()->getResult() as $image) {
            $imagesByEntityId[$image->getEntityId()][] = $image;
        }

        return $imagesByEntityId;
    }

    /**
     * @param int[] $entityIds
     * @return array<int, int>
     */
    public function getImageCountsIndexedByEntityId(array $entityIds, string $entityName, ?string $type): array
    {
        $imagesCountsByEntityId = array_fill_keys($entityIds, 0);
        $queryBuilder = $this->entityManager->getRepository(Image::class)
            ->createQueryBuilder('i')
            ->select('i.entityId AS entityId, COUNT(i.id) AS imagesCount')
            ->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.entityId IN (:entities)')->setParameter('entities', $entityIds)
            ->groupBy('i.entityId');

        if ($type === null) {
            $queryBuilder->andWhere('i.type IS NULL');
        } else {
            $queryBuilder->andWhere('i.type = :type')->setParameter('type', $type);
        }

        /** @var array{entityId: int|string, imagesCount: int|string} $imageCount */
        foreach ($queryBuilder->getQuery()->getArrayResult() as $imageCount) {
            $imagesCountsByEntityId[(int)$imageCount['entityId']] = (int)$imageCount['imagesCount'];
        }

        return $imagesCountsByEntityId;
    }

    /**
     * @return int[]
     */
    public function getEntityIdsWithImageByEntityName(string $entityName): array
    {
        $entityIds = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT i.entityId')
            ->from($this->entityNameResolver->resolve(Image::class), 'i')
            ->where('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.type IS NULL')
            ->getQuery()
            ->getSingleColumnResult();

        return array_map(static fn ($entityId) => (int)$entityId, $entityIds);
    }
}
