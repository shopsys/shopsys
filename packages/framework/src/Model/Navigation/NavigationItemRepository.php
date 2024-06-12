<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Navigation\Exception\NavigationItemNotFoundException;

class NavigationItemRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepository(): EntityRepository
    {
        return $this->em->getRepository(NavigationItem::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem|null
     */
    public function findById(int $id): ?NavigationItem
    {
        return $this->getEntityRepository()->find($id);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem
     */
    public function getById(int $id): NavigationItem
    {
        $item = $this->findById($id);

        if ($item === null) {
            throw new NavigationItemNotFoundException(
                sprintf('Navigation item with ID %s not found', $id),
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
