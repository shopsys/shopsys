<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\CategoryDomain;

class HorizontalMenuItemCategoryRepository
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
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getSortedHorizontalMenuItemCategoriesByHorizontalMenuItemQueryBuilder(HorizontalMenuItem $horizontalMenuItem): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('hmic')
            ->from(HorizontalMenuItemCategory::class, 'hmic')
            ->where('hmic.horizontalMenuItem = :horizontalMenuItem')
            ->setParameter('horizontalMenuItem', $horizontalMenuItem)
            ->orderBy('hmic.columnNumber', 'asc')
            ->addOrderBy('hmic.position', 'asc');
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @return \App\Model\HorizontalMenu\HorizontalMenuItemCategory[]
     */
    public function getSortedHorizontalMenuItemCategoriesByHorizontalMenuItem(HorizontalMenuItem $horizontalMenuItem): array
    {
        return $this->getSortedHorizontalMenuItemCategoriesByHorizontalMenuItemQueryBuilder($horizontalMenuItem)
            ->getQuery()->execute();
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     * @param int $domainId
     * @return \App\Model\HorizontalMenu\HorizontalMenuItemCategory[]
     */
    public function getSortedVisibledHorizontalMenuItemCategoriesByHorizontalMenuItem(
        HorizontalMenuItem $horizontalMenuItem,
        int $domainId
    ): array {
        $queryBuilder = $this->getSortedHorizontalMenuItemCategoriesByHorizontalMenuItemQueryBuilder($horizontalMenuItem);

        $queryBuilder->join(CategoryDomain::class, 'cd', Join::WITH, 'cd.category = hmic.category')
            ->andWhere('cd.domainId = :domainId')
            ->andWhere('cd.visible = TRUE')
            ->setParameter('domainId', $domainId);

        return $queryBuilder->getQuery()->getResult();
    }
}
