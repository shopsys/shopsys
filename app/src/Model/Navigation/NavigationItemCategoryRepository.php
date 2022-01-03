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
     * @param \App\Model\Navigation\NavigationItem[] $navigationItems
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getSortedNavigationItemCategoriesByNavigationItemQueryBuilder(array $navigationItems): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('nic')
            ->from(NavigationItemCategory::class, 'nic')
            ->where('nic.navigationItem IN(:navigationItems)')
            ->setParameter('navigationItems', $navigationItems)
            ->orderBy('nic.columnNumber', 'asc')
            ->addOrderBy('nic.position', 'asc');
    }

    /**
     * @param \App\Model\Navigation\NavigationItem[] $navigationItems
     * @return \App\Model\Navigation\NavigationItemCategory[]
     */
    public function getSortedNavigationItemCategoriesByNavigationItems(array $navigationItems): array
    {
        return $this->getSortedNavigationItemCategoriesByNavigationItemQueryBuilder($navigationItems)
            ->getQuery()->execute();
    }

    /**
     * @param \App\Model\Navigation\NavigationItem[] $navigationItems
     * @param int $domainId
     * @return \App\Model\Navigation\NavigationItemCategory[]
     */
    public function getSortedVisibleNavigationItemCategoriesByNavigationItems(
        array $navigationItems,
        int $domainId
    ): array {
        $queryBuilder = $this->getSortedNavigationItemCategoriesByNavigationItemQueryBuilder($navigationItems);

        $queryBuilder->join(CategoryDomain::class, 'cd', Join::WITH, 'cd.category = nic.category')
            ->andWhere('cd.domainId = :domainId')
            ->andWhere('cd.visible = TRUE')
            ->setParameter('domainId', $domainId);

        return $queryBuilder->getQuery()->getResult();
    }
}
