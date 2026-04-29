<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Search\SearchSetting;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Category\Exception\RootCategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class CategoryRepository extends NestedTreeRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
        $classMetadata = $this->em->getClassMetadata(Category::class);

        parent::__construct($this->em, $classMetadata);
    }

    protected function getCategoryRepository(): EntityRepository
    {
        return $this->em->getRepository(Category::class);
    }

    protected function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getCategoryRepository()
            ->createQueryBuilder('c')
            ->where('c.parent IS NOT NULL')
            ->orderBy('c.lft');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAll(): array
    {
        return $this->getAllQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $selectedCategories
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllCategoriesOfCollapsedTree(array $selectedCategories): array
    {
        $openedParentsQueryBuilder = $this->getCategoryRepository()
            ->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.parent IS NULL');

        foreach ($selectedCategories as $selectedCategory) {
            $where = sprintf('c.lft < %d AND c.rgt > %d', $selectedCategory->getLft(), $selectedCategory->getRgt());
            $openedParentsQueryBuilder->orWhere($where);
        }

        $openedParentIds = array_column($openedParentsQueryBuilder->getQuery()->getScalarResult(), 'id');

        return $this->getAllQueryBuilder()
            ->select('c, cd, ct')
            ->join('c.domains', 'cd')
            ->join('c.translations', 'ct')
            ->where('c.parent IN (:openedParentIds)')
            ->setParameter('openedParentIds', $openedParentIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return string[]
     */
    public function getFullPathsIndexedByIdsForDomain(int $domainId, string $locale): array
    {
        $queryBuilder = $this->getPreOrderTreeTraversalForAllCategoriesByDomainQueryBuilder($domainId, $locale);

        return $this->getFullPathsByQueryBuilder($queryBuilder);
    }

    /**
     * @return string[]
     */
    public function getFullPathsIndexedByIds(string $locale): array
    {
        $queryBuilder = $this->getPreOrderTreeTraversalForAllCategoriesQueryBuilder($locale);

        return $this->getFullPathsByQueryBuilder($queryBuilder);
    }

    /**
     * @return string[]
     */
    protected function getFullPathsByQueryBuilder(QueryBuilder $queryBuilder): array
    {
        $rows = $queryBuilder->select('c.id, IDENTITY(c.parent) AS parentId, ct.name')->getQuery()->getScalarResult();

        $fullPathsById = [];

        foreach ($rows as $row) {
            if (array_key_exists($row['parentId'], $fullPathsById)) {
                $fullPathsById[$row['id']] = $fullPathsById[$row['parentId']] . ' - ' . $row['name'];
            } else {
                $fullPathsById[$row['id']] = $row['name'];
            }
        }

        return $fullPathsById;
    }

    protected function getPreOrderTreeTraversalForAllCategoriesQueryBuilder(string $locale): QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        $queryBuilder
            ->andWhere('c.level >= 1')
            ->orderBy('c.lft');

        return $queryBuilder;
    }

    /**
     * @return int[]
     */
    public function getAllIds(): array
    {
        $result = $this->getAllQueryBuilder()
            ->select('c.id')
            ->getQuery()
            ->getScalarResult();

        return array_map('current', $result);
    }

    public function getRootCategory(): Category
    {
        return $this->inMemoryCache->getOrSaveValue(
            'rootCategory',
            function () {
                $rootCategory = $this->getCategoryRepository()->findOneBy(['parent' => null]);

                if ($rootCategory === null) {
                    throw new RootCategoryNotFoundException('Root category not found');
                }

                return $rootCategory;
            },
            'rootCategory',
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllTranslatedWithoutBranch(Category $categoryBranch, string $locale): array
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        return $queryBuilder->andWhere('c.lft < :branchLft OR c.rgt > :branchRgt')
            ->setParameter('branchLft', $categoryBranch->getLft())
            ->setParameter('branchRgt', $categoryBranch->getRgt())
            ->getQuery()
            ->getResult();
    }

    public function findById(int $categoryId): ?Category
    {
        /** @var \Shopsys\FrameworkBundle\Model\Category\Category|null $category */
        $category = $this->getCategoryRepository()->find($categoryId);

        if ($category !== null && $category->getParent() === null) {
            // Copies logic from getAllQueryBuilder() - excludes root category
            // Query builder is not used to be able to get the category from identity map if it was loaded previously
            return null;
        }

        return $category;
    }

    public function getById(int $categoryId): Category
    {
        $category = $this->findById($categoryId);

        if ($category === null) {
            $message = 'Category with ID ' . $categoryId . ' not found.';

            throw new CategoryNotFoundException($message);
        }

        return $category;
    }

    public function getOneByUuid(string $uuid): Category
    {
        $category = $this->getCategoryRepository()->findOneBy(['uuid' => $uuid]);

        if ($category === null) {
            throw new CategoryNotFoundException('Category with UUID ' . $uuid . ' does not exist.');
        }

        return $category;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getPreOrderTreeTraversalForAllCategories(string $locale): array
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        $queryBuilder
            ->andWhere('c.level >= 1')
            ->orderBy('c.lft');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getPreOrderTreeTraversalForVisibleCategoriesByDomain(int $domainId, string $locale): array
    {
        $queryBuilder = $this->getPreOrderTreeTraversalForAllCategoriesByDomainQueryBuilder($domainId, $locale);

        $queryBuilder->andWhere('cd.visible = TRUE');

        return $queryBuilder->getQuery()->getResult();
    }

    public function getPreOrderTreeTraversalForAllCategoriesByDomainQueryBuilder(
        int $domainId,
        string $locale,
    ): QueryBuilder {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        $queryBuilder
            ->join('c.domains', 'cd')
            ->andWhere('c.level >= 1')
            ->andWhere('cd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->orderBy('c.lft');

        return $queryBuilder;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getTranslatedVisibleSubcategoriesByDomain(
        Category $parentCategory,
        DomainConfig $domainConfig,
    ): array {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        $this->addTranslation($queryBuilder, $domainConfig->getLocale());

        $queryBuilder
            ->andWhere('c.parent = :parentCategory')
            ->setParameter('parentCategory', $parentCategory);

        return $queryBuilder->getQuery()->getResult();
    }

    public function addTranslation(QueryBuilder $categoriesQueryBuilder, string $locale): void
    {
        $categoriesQueryBuilder
            ->addSelect('ct')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->setParameter('locale', $locale);
    }

    public function getAllVisibleByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder()
            ->join('c.domains', 'cd')
            ->andWhere('cd.domainId = :domainId')
            ->andWhere('cd.visible = TRUE');

        $queryBuilder->setParameter('domainId', $domainId);

        return $queryBuilder;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllVisibleChildrenByCategoryAndDomainId(Category $category, int $domainId): array
    {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->andWhere('c.parent = :category')
            ->setParameter('category', $category);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @return int[]
     */
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

        $queryBuilder = $this->productRepository->getAllListableQueryBuilder($domainId, $pricingGroup)
            ->join(
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

        foreach ($queryBuilder->getQuery()->getArrayResult() as $result) {
            $listableProductCountsIndexedByCategoryId[$result['categoryId']] = $result['productCount'];
        }

        return $listableProductCountsIndexedByCategoryId;
    }

    public function filterBySearchText(QueryBuilder $queryBuilder, string $searchText): void
    {
        $queryBuilder->andWhere(
            'NORMALIZED(ct.name) LIKE NORMALIZED(:searchText) OR NORMALIZED(cd.seoH1) LIKE NORMALIZED(:searchText) OR NORMALIZED(cd.seoTitle) LIKE NORMALIZED(:searchText) OR NORMALIZED(cd.seoMetaDescription) LIKE NORMALIZED(:searchText)',
        );

        if (mb_strlen($searchText) < SearchSetting::SIMPLE_SEARCH_THRESHOLD) {
            $queryBuilder->setParameter('searchText', $searchText . '%');
        } else {
            $queryBuilder->setParameter('searchText', $this->databaseSearchingHelper->getFullTextLikeSearchString($searchText));
        }
    }

    public function findProductMainCategoryOnDomain(Product $product, int $domainId): ?Category
    {
        $qb = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->join(
                ProductCategoryDomain::class,
                'pcd',
                Join::WITH,
                'pcd.product = :product
                    AND pcd.category = c
                    AND pcd.domainId = :domainId',
            )
            ->orderBy('c.level DESC, c.lft')
            ->setMaxResults(1);

        $qb->setParameter('domainId', $domainId)
            ->setParameter('product', $product);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getProductMainCategoryOnDomain(Product $product, int $domainId): Category
    {
        $productMainCategory = $this->findProductMainCategoryOnDomain($product, $domainId);

        if ($productMainCategory === null) {
            throw new CategoryNotFoundException(
                sprintf(
                    'Main category for product id `%d` and domain id `%d` was not found',
                    $product->getId(),
                    $domainId,
                ),
            );
        }

        return $productMainCategory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleCategoriesInPathFromRootOnDomain(Category $category, int $domainId): array
    {
        $qb = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->andWhere('c.lft <= :lft')->setParameter('lft', $category->getLft())
            ->andWhere('c.rgt >= :rgt')->setParameter('rgt', $category->getRgt())
            ->orderBy('c.lft');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return string[]
     */
    public function getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(
        Product $product,
        DomainConfig $domainConfig,
        ?string $locale = null,
    ): array {
        $queryBuilder = $this->getAllQueryBuilder();
        $domainId = $domainConfig->getId();
        $locale = $locale ?? $domainConfig->getLocale();
        $mainCategory = $this->getProductMainCategoryOnDomain($product, $domainId);

        $this->addTranslation($queryBuilder, $locale);
        $queryBuilder
            ->select('ct.name')
            ->andWhere('c.lft <= :mainCategoryLft AND c.rgt >= :mainCategoryRgt')
            ->setParameter('mainCategoryLft', $mainCategory->getLft())
            ->setParameter('mainCategoryRgt', $mainCategory->getRgt());
        $result = $queryBuilder->getQuery()->getScalarResult();

        return array_map('current', $result);
    }

    /**
     * @param int[] $categoryIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     */
    public function getCategoriesByIds(array $categoryIds): array
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $queryBuilder
            ->andWhere('c.id IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    protected function getCategoriesWithVisibleChildrenQueryBuilder(array $categories, int $domainId): QueryBuilder
    {
        return $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->join('c.children', 'cc')
            ->join('cc.domains', 'ccd')
            ->andWhere('ccd.domainId = :domainId')
            ->andWhere('ccd.visible = TRUE')
            ->andWhere('c IN (:categories)')
            ->setParameter('categories', $categories);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategoriesWithVisibleChildren(array $categories, int $domainId): array
    {
        return $this->getCategoriesWithVisibleChildrenQueryBuilder($categories, $domainId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getAllTranslated(string $locale): array
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return array<int, string>
     */
    public function getVisibilityOfCategoriesIndexedById(int $domainCount): array
    {
        $visibilityOfCategoriesIndexedById = [];

        $result = $this->getAllQueryBuilder()
            ->select('c.id, COUNT(cd.id) AS domainCount')
            ->join('c.domains', 'cd')
            ->andWhere('cd.visible = TRUE')
            ->groupBy('c.id')
            ->getQuery()
            ->getArrayResult();

        foreach ($result as $row) {
            $visibilityOfCategoriesIndexedById[$row['id']] = $row['domainCount'] === $domainCount ? 'all' : 'partial';
        }

        return $visibilityOfCategoriesIndexedById;
    }
}
