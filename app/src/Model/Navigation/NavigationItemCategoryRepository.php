<?php

declare(strict_types=1);

namespace App\Model\Navigation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Navigation\NavigationItemCategory[]
     */
    public function getSortedVisibleNavigationItemCategoriesByNavigationItems(
        array $navigationItems,
        DomainConfig $domainConfig
    ): array {
        $queryBuilder = $this->getSortedNavigationItemCategoriesByNavigationItemQueryBuilder($navigationItems);

        $queryBuilder
            ->addSelect('c, cd, ct')
            ->join('nic.category', 'c')
            ->join('c.domains', 'cd')
            ->join('c.translations', 'ct')
            ->andWhere('cd.domainId = :domainId')
            ->andWhere('ct.locale = :locale')
            ->andWhere('cd.visible = TRUE')
            ->setParameter('domainId', $domainConfig->getId())
            ->setParameter('locale', $domainConfig->getLocale());

        return $queryBuilder->getQuery()->getResult();
    }
}
