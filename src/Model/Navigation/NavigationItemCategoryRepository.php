<?php

declare(strict_types=1);

namespace App\Model\Navigation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\CategoryDomain;

class NavigationItemCategoryRepository
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
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getSortedNavigationItemCategoriesByNavigationItemQueryBuilder(NavigationItem $navigationItem): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('nic')
            ->from(NavigationItemCategory::class, 'nic')
            ->where('nic.navigationItem = :navigationItem')
            ->setParameter('navigationItem', $navigationItem)
            ->orderBy('nic.columnNumber', 'asc')
            ->addOrderBy('nic.position', 'asc');
    }

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @return \App\Model\Navigation\NavigationItemCategory[]
     */
    public function getSortedNavigationItemCategoriesByNavigationItem(NavigationItem $navigationItem): array
    {
        return $this->getSortedNavigationItemCategoriesByNavigationItemQueryBuilder($navigationItem)
            ->getQuery()->execute();
    }

    /**
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     * @param int $domainId
     * @return \App\Model\Navigation\NavigationItemCategory[]
     */
    public function getSortedVisibleNavigationItemCategoriesByNavigationItem(
        NavigationItem $navigationItem,
        int $domainId
    ): array {
        $queryBuilder = $this->getSortedNavigationItemCategoriesByNavigationItemQueryBuilder($navigationItem);

        $queryBuilder->join(CategoryDomain::class, 'cd', Join::WITH, 'cd.category = nic.category')
            ->andWhere('cd.domainId = :domainId')
            ->andWhere('cd.visible = TRUE')
            ->setParameter('domainId', $domainId);

        return $queryBuilder->getQuery()->getResult();
    }
}
