<?php

namespace Shopsys\FrameworkBundle\Component\Grid\Ordering;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException;

class GridOrderingFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param string $entityClass
     * @param array $rowIds
     */
    public function saveOrdering($entityClass, array $rowIds)
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
     * @param string $entityClass
     * @return mixed
     */
    protected function getEntityRepository($entityClass)
    {
        $interfaces = class_implements($entityClass);
        if (array_key_exists(OrderableEntityInterface::class, $interfaces)) {
            return $this->em->getRepository($entityClass);
        }
        throw new EntityIsNotOrderableException();
    }
}
