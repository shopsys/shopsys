<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\PersooBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class PersooCategoryRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     */
    public function __construct(
        protected readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategories(
        DomainConfig $domainConfig,
        ?int $lastSeekId,
        int $maxResults,
    ): iterable {
        $queryBuilder = $this->categoryRepository->getPreOrderTreeTraversalForAllCategoriesByDomainQueryBuilder($domainConfig->getId(), $domainConfig->getLocale())
            ->andWhere('cd.visible = TRUE')
            ->setMaxResults($maxResults);

        if ($lastSeekId !== null) {
            $queryBuilder->andWhere('c.id > :lastCategoryId')->setParameter('lastCategoryId', $lastSeekId);
        }

        return $queryBuilder->getQuery()->execute();
    }
}
