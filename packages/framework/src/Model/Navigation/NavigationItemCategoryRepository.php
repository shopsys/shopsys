<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class NavigationItemCategoryRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem[] $navigationItems
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getSortedNavigationItemCategoriesByNavigationItemQueryBuilder(
        array $navigationItems,
    ): QueryBuilder {
        return $this->em->createQueryBuilder()
            ->select('nic')
            ->from(NavigationItemCategory::class, 'nic')
            ->where('nic.navigationItem IN(:navigationItems)')
            ->setParameter('navigationItems', $navigationItems)
            ->orderBy('nic.columnNumber', 'asc')
            ->addOrderBy('nic.position', 'asc');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem[] $navigationItems
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemCategory[]
     */
    public function getSortedNavigationItemCategoriesByNavigationItems(array $navigationItems): array
    {
        return $this->getSortedNavigationItemCategoriesByNavigationItemQueryBuilder($navigationItems)
            ->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem[] $navigationItems
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemCategory[]
     */
    public function getSortedVisibleNavigationItemCategoriesByNavigationItems(
        array $navigationItems,
        DomainConfig $domainConfig,
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
