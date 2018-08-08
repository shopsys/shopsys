<?php

namespace Shopsys\FrameworkBundle\Component\Grid\Ordering;

use Doctrine\ORM\EntityManagerInterface;

class GridOrderingFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingService
     */
    protected $gridOrderingService;

    public function __construct(EntityManagerInterface $em, GridOrderingService $gridOrderingService)
    {
        $this->em = $em;
        $this->gridOrderingService = $gridOrderingService;
    }
    
    public function saveOrdering(string $entityClass, array $rowIds): void
    {
        $entityRepository = $this->getEntityRepository($entityClass);
        $position = 0;

        foreach ($rowIds as $rowId) {
            $entity = $entityRepository->find($rowId);
            $this->gridOrderingService->setPosition($entity, $position++);
        }

        $this->em->flush();
    }

    /**
     * @return mixed
     */
    protected function getEntityRepository(string $entityClass)
    {
        $interfaces = class_implements($entityClass);
        if (array_key_exists(OrderableEntityInterface::class, $interfaces)) {
            return $this->em->getRepository($entityClass);
        } else {
            throw new \Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException();
        }
    }
}
