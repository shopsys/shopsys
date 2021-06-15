<?php

declare(strict_types=1);

namespace App\Model\Navigation;

use App\Model\Navigation\Exception\NavigationItemNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class NavigationItemRepository
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
        return $this->em->getRepository(NavigationItem::class);
    }

    /**
     * @param int $id
     * @return \App\Model\Navigation\NavigationItem|null
     */
    public function findById(int $id): ?NavigationItem
    {
        return $this->getEntityRepository()->find($id);
    }

    /**
     * @param int $id
     * @return \App\Model\Navigation\NavigationItem
     */
    public function getById(int $id): NavigationItem
    {
        $item = $this->findById($id);

        if ($item === null) {
            throw new NavigationItemNotFoundException(
                sprintf('Navigation item with ID %s not found', $id)
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
            ->select('ni')
            ->from(NavigationItem::class, 'ni')
            ->orderBy('ni.position', 'asc');
    }
}
