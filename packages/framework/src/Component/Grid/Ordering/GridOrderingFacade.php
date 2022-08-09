<?php

namespace Shopsys\FrameworkBundle\Component\Grid\Ordering;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException;

class GridOrderingFacade
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
     * @param class-string $entityClass
     * @param array<int|string> $rowIds
     */
    public function saveOrdering(string $entityClass, array $rowIds): void
    {
        $entityRepository = $this->getEntityRepository($entityClass);
        $position = 0;

        foreach ($rowIds as $rowId) {
            $entity = $entityRepository->find($rowId);
            $entity->setPosition($position++);
        }

        $this->em->flush();
    }

    /**
     * @template T of object
     * @param class-string<T> $entityClass
     * @return \Doctrine\Persistence\ObjectRepository<T>
     */
    protected function getEntityRepository(string $entityClass): ObjectRepository
    {
        $interfaces = class_implements($entityClass);
        if (array_key_exists(OrderableEntityInterface::class, $interfaces)) {
            return $this->em->getRepository($entityClass);
        }
        throw new EntityIsNotOrderableException();
    }
}
