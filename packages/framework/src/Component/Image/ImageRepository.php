<?php

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;

class ImageRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getImageRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Image::class);
    }

    /**
     * @param string|null $type
     */
    public function findImageByEntity(string $entityName, int $entityId, ?string $type): ?\Shopsys\FrameworkBundle\Component\Image\Image
    {
        $image = $this->getImageRepository()->findOneBy(
            [
                'entityName' => $entityName,
                'entityId' => $entityId,
                'type' => $type,
            ],
            [
                'position' => 'asc',
                'id' => 'asc',
            ]
        );

        return $image;
    }

    /**
     * @param string|null $type
     */
    public function getImageByEntity(string $entityName, int $entityId, ?string $type): \Shopsys\FrameworkBundle\Component\Image\Image
    {
        $image = $this->findImageByEntity($entityName, $entityId, $type);
        if ($image === null) {
            $message = 'Image of type "' . ($type ?: 'NULL') . '" not found for entity "' . $entityName . '" with ID ' . $entityId;
            throw new \Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException($message);
        }

        return $image;
    }

    /**
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityIndexedById(string $entityName, int $entityId, ?string $type): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('i')
            ->from(Image::class, 'i', 'i.id')
            ->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.entityId = :entityId')->setParameter('entityId', $entityId)
            ->addOrderBy('i.position', 'asc')
            ->addOrderBy('i.id', 'asc');

        if ($type === null) {
            $queryBuilder->andWhere('i.type IS NULL');
        } else {
            $queryBuilder->andWhere('i.type = :type')->setParameter('type', $type);
        }

        return $queryBuilder->getQuery()->execute();
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
    
    public function getById(int $imageId): \Shopsys\FrameworkBundle\Component\Image\Image
    {
        $image = $this->getImageRepository()->find($imageId);

        if ($image === null) {
            throw new \Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException('Image with ID ' . $imageId . ' does not exist.');
        }

        return $image;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getMainImagesByEntitiesIndexedByEntityId(array $entities, string $entityName): array
    {
        $queryBuilder = $this->getImageRepository()
            ->createQueryBuilder('i')
            ->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.entityId IN (:entities)')->setParameter('entities', $entities)
            ->addOrderBy('i.position', 'desc')
            ->addOrderBy('i.id', 'desc');

        $imagesByEntityId = [];
        foreach ($queryBuilder->getQuery()->execute() as $image) {
            /* @var $image \Shopsys\FrameworkBundle\Component\Image\Image */
            $imagesByEntityId[$image->getEntityId()] = $image;
        }

        return $imagesByEntityId;
    }
}
