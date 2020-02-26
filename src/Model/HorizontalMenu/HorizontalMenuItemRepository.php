<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class HorizontalMenuItemRepository
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getEntityRepository(): EntityRepository
    {
        return $this->em->getRepository(HorizontalMenuItem::class);
    }

    /**
     * @param int $id
     * @return \App\Model\HorizontalMenu\HorizontalMenuItem|null
     */
    public function findById(int $id): ?HorizontalMenuItem
    {
        return $this->getEntityRepository()->find($id);
    }

    /**
     * @param int $id
     * @return \App\Model\HorizontalMenu\HorizontalMenuItem
     */
    public function getById(int $id): HorizontalMenuItem
    {
        $item = $this->findById($id);

        if ($item === null) {
            throw new \App\Model\HorizontalMenu\Exception\HorizontalMenuItemNotFoundException(
                sprintf('Horizontal menu iten with ID %s not found', $id)
            );
        }

        return $item;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedItemsQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('hmi')
            ->from(HorizontalMenuItem::class, 'hmi')
            ->orderBy('hmi.position', 'asc');
    }
}
