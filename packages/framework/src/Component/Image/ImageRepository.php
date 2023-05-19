<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;

class ImageRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getImageRepository()
    {
        return $this->em->getRepository(Image::class);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image|null
     */
    public function findImageByEntity($entityName, $entityId, $type)
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

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
    public function getImageByEntity($entityName, $entityId, $type)
    {
        $image = $this->findImageByEntity($entityName, $entityId, $type);

        if ($image === null) {
            $message = 'Image of type "' . ($type ?: 'NULL') . '" not found for entity "' . $entityName . '" with ID ' . $entityId;

            throw new ImageNotFoundException($message);
        }

        return $image;
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getImagesByEntityIndexedById($entityName, $entityId, $type)
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('i, it')
            ->from(Image::class, 'i', 'i.id')
            ->join('i.translations', 'it')
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
     * @param string $entityName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getAllImagesByEntity($entityName, $entityId)
    {
        return $this->getImageRepository()->findBy([
            'entityName' => $entityName,
            'entityId' => $entityId,
        ]);
    }

    /**
     * @param int $imageId
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
    public function getById($imageId)
    {
        $image = $this->getImageRepository()->find($imageId);

        if ($image === null) {
            throw new ImageNotFoundException('Image with ID ' . $imageId . ' does not exist.');
        }

        return $image;
    }

    /**
     * @param array $entitiesOrEntityIds
     * @param string $entityName
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getMainImagesByEntitiesIndexedByEntityId(array $entitiesOrEntityIds, $entityName)
    {
        $queryBuilder = $this->getImageRepository()
            ->createQueryBuilder('i')
            ->andWhere('i.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('i.entityId IN (:entities)')->setParameter('entities', $entitiesOrEntityIds)
            ->addOrderBy('i.position', 'desc')
            ->addOrderBy('i.id', 'desc');

        $imagesByEntityId = [];
        /** @var \Shopsys\FrameworkBundle\Component\Image\Image $image */
        foreach ($queryBuilder->getQuery()->execute() as $image) {
            $imagesByEntityId[$image->getEntityId()] = $image;
        }

        return $imagesByEntityId;
    }
}
