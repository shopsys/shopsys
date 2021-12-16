<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Image;

use App\Component\Image\Image;
use Doctrine\ORM\EntityManagerInterface;

class ImageRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int[] $entityIds
     * @param string $entityName
     * @param string|null $type
     * @return \App\Component\Image\Image[][]
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

        /** @var \App\Component\Image\Image $image */
        foreach ($queryBuilder->getQuery()->execute() as $image) {
            $imagesByEntityId[$image->getEntityId()][] = $image;
        }

        return $imagesByEntityId;
    }
}
