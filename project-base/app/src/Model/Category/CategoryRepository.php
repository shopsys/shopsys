<?php

declare(strict_types=1);

namespace App\Model\Category;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository as BaseCategoryRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper $orderByCollationHelper, \Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper $databaseSearchingHelper, \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache)
 * @method \App\Model\Category\Category[] getAll()
 * @method \App\Model\Category\Category[] getAllCategoriesOfCollapsedTree(\App\Model\Category\Category[] $selectedCategories)
 * @method \App\Model\Category\Category getRootCategory()
 * @method \App\Model\Category\Category[] getAllTranslatedWithoutBranch(\App\Model\Category\Category $categoryBranch, string $locale)
 * @method \App\Model\Category\Category|null findById(int $categoryId)
 * @method \App\Model\Category\Category getById(int $categoryId)
 * @method \App\Model\Category\Category getOneByUuid(string $uuid)
 * @method \App\Model\Category\Category[] getPreOrderTreeTraversalForAllCategories(string $locale)
 * @method \App\Model\Category\Category[] getPreOrderTreeTraversalForVisibleCategoriesByDomain(int $domainId, string $locale)
 * @method \App\Model\Category\Category[] getTranslatedVisibleSubcategoriesByDomain(\App\Model\Category\Category $parentCategory, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category[] getAllVisibleChildrenByCategoryAndDomainId(\App\Model\Category\Category $category, int $domainId)
 * @method \App\Model\Category\Category|null findProductMainCategoryOnDomain(\App\Model\Product\Product $product, int $domainId)
 * @method \App\Model\Category\Category getProductMainCategoryOnDomain(\App\Model\Product\Product $product, int $domainId)
 * @method \App\Model\Category\Category[] getVisibleCategoriesInPathFromRootOnDomain(\App\Model\Category\Category $category, int $domainId)
 * @method string[] getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig, string|null $locale = null)
 * @method \App\Model\Category\Category[] getCategoriesByIds(int[] $categoryIds)
 * @method \App\Model\Category\Category[] getCategoriesWithVisibleChildren(\App\Model\Category\Category[] $categories, int $domainId)
 * @method \App\Model\Category\Category[] getAllTranslated(string $locale)
 */
class CategoryRepository extends BaseCategoryRepository
{
    /**
     * @param \App\Model\Category\Category[] $categories
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     * @return int[]
     */
    #[Override]
    public function getListableProductCountsIndexedByCategoryId(
        array $categories,
        PricingGroup $pricingGroup,
        int $domainId,
    ): array {
        if (count($categories) === 0) {
            return [];
        }
        $listableProductCountsIndexedByCategoryId = [];

        foreach ($categories as $category) {
            // Initialize array with zeros as categories without found products will not be represented in result rows
            $listableProductCountsIndexedByCategoryId[$category->getId()] = 0;
        }

        $queryBuilder = $this->productRepository->getAllListableQueryBuilder($domainId, $pricingGroup);
        $queryBuilder->join(
            ProductCategoryDomain::class,
            'pcd',
            Join::WITH,
            'pcd.product = p
                 AND pcd.category IN (:categories)
                 AND pcd.domainId = :domainId',
        )
            ->select('IDENTITY(pcd.category) AS categoryId, COUNT(p) AS productCount')
            ->setParameter('categories', $categories)
            ->setParameter('domainId', $domainId)
            ->groupBy('pcd.category')
            ->resetDQLPart('orderBy');

        $results = $queryBuilder->getQuery()->getArrayResult();

        foreach ($results as $result) {
            $listableProductCountsIndexedByCategoryId[$result['categoryId']] = $result['productCount'];
        }

        return $listableProductCountsIndexedByCategoryId;
    }

    /**
     * Thanks to joining "c.domains" instead of "CategoryDomain::class",
     * the category domains can be eager loaded (by adding "cd" to "select" part), but are still excluded from the result array
     *
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    #[Override]
    public function getAllVisibleByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder()
            ->join('c.domains', 'cd', Join::WITH, 'cd.domainId = :domainId AND cd.visible = TRUE');

        $queryBuilder->setParameter('domainId', $domainId);

        return $queryBuilder;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[]
     */
    public function getAllVisibleChildrenByCategoryAndDomainConfig(
        Category $category,
        DomainConfig $domainConfig,
    ): array {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId())
            ->addSelect('cd')
            ->andWhere('c.parent = :category')
            ->setParameter('category', $category);
        $this->addTranslation($queryBuilder, $domainConfig->getLocale());

        return $queryBuilder->getQuery()->execute();
    }
}
