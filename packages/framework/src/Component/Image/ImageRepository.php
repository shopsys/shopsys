<?php

namespace Shopsys\FrameworkBundle\Component\Image;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;

class ImageRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\FrameworkBundle\Component\Image\Image>
     */
    protected function getImageRepository(): EntityRepository
    {
        return $this->em->getRepository(Image::class);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image|null
     */
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
            ]
        );
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\Image\Image
     */
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
     * @param string $entityName
     * @param int $entityId
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
     * @param string $entityName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Image\Image[]
     */
    public function getAllImagesByEntity(string $entityName, int $entityId): array
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
    public function getById(int $imageId): Image
    {
        $image = $this->getImageRepository()->find($imageId);

        if ($image === null) {
            throw new ImageNotFoundException('Image with ID ' . $imageId . ' does not exist.');
        }

        return $image;
    }

    /**
     * @param array<object|int> $entitiesOrEntityIds
     * @param string $entityName
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
        foreach ($queryBuilder->getQuery()->execute() as $image) {
            $imagesByEntityId[$image->getEntityId()] = $image;
        }

        return $imagesByEntityId;
    }
}
