<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;

class ImageRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getImageRepository(): EntityRepository
    {
        return $this->em->getRepository(Image::class);
    }

    public function findImageByEntity(string $entityName, int $entityId, ?string $type): ?Image
    {
        return $this->getImageRepository()->findOneBy(
            [
                'entityName' => $entityName,
                'entityId' => $entityId,
                'type' => $type,
            ],
            [
                'position' => 'asc',
                'id' => 'asc',
            ],
        );
    }

    public function getImageByEntity(string $entityName, int $entityId, ?string $type): Image
    {
        $image = $this->findImageByEntity($entityName, $entityId, $type);

        if ($image === null) {
            $message = 'Image of type "' . ($type ?: 'NULL') . '" not found for entity "' . $entityName . '" with ID ' . $entityId;

            throw new ImageNotFoundException($message);
        }

        return $image;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityIndexedById(string $entityName, int $entityId, ?string $type): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('i, it')
            ->from(Image::class, 'i', 'i.id')
            ->leftJoin('i.translations', 'it')
            ->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.entityId = :entityId')->setParameter('entityId', $entityId)
            ->addOrderBy('i.position', 'asc')
            ->addOrderBy('i.id', 'asc');

        if ($type === null) {
            $queryBuilder->andWhere('i.type IS NULL');
        } else {
            $queryBuilder->andWhere('i.type = :type')->setParameter('type', $type);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getAllImagesByEntity(string $entityName, int $entityId): array
    {
        return $this->getImageRepository()->findBy([
            'entityName' => $entityName,
            'entityId' => $entityId,
        ]);
    }

    public function getById(int $imageId): Image
    {
        $image = $this->getImageRepository()->find($imageId);

        if ($image === null) {
            throw new ImageNotFoundException('Image with ID ' . $imageId . ' does not exist.');
        }

        return $image;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getMainImagesByEntitiesIndexedByEntityId(array $entitiesOrEntityIds, string $entityName): array
    {
        $queryBuilder = $this->getImageRepository()
            ->createQueryBuilder('i')
            ->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.entityId IN (:entities)')->setParameter('entities', $entitiesOrEntityIds)
            ->addOrderBy('i.position', 'desc')
            ->addOrderBy('i.id', 'desc');

        $imagesByEntityId = [];

        /** @var \Shopsys\FrameworkBundle\Component\Image\Image $image */
        foreach ($queryBuilder->getQuery()->getResult() as $image) {
            $imagesByEntityId[$image->getEntityId()] = $image;
        }

        return $imagesByEntityId;
    }

    public function getNewImagePosition(string $entityName, int $entityId, ?string $type = null): int
    {
        $queryBuilder = $this->getImageRepository()
            ->createQueryBuilder('i', 'i.id')
            ->select('MAX(i.position)')
            ->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.entityId = :entityId')->setParameter('entityId', $entityId);

        if ($type === null) {
            $queryBuilder->andWhere('i.type IS NULL');
        } else {
            $queryBuilder->andWhere('i.type = :type')->setParameter('type', $type);
        }

        $position = $queryBuilder->getQuery()->getSingleScalarResult();

        return $position === null ? 0 : ++$position;
    }
}
