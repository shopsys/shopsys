<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class CategoryRepository extends NestedTreeRepository
{
    const MOVE_DOWN_TO_BOTTOM = true;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    public function __construct(
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        EntityNameResolver $entityNameResolver
    ) {
        $this->em = $em;
        $this->productRepository = $productRepository;

        $resolvedClassName = $entityNameResolver->resolve(Category::class);
        $classMetadata = $this->em->getClassMetadata($resolvedClassName);
        parent::__construct($this->em, $classMetadata);
    }

    protected function getCategoryRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Category::class);
    }

    protected function getAllQueryBuilder(): \Doctrine\ORM\QueryBuilder
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
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getFullPathsIndexedByIdsForDomain(int $domainId, string $locale): array
    {
        $queryBuilder = $this->getPreOrderTreeTraversalForAllCategoriesByDomainQueryBuilder($domainId, $locale);

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

    public function getRootCategory(): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        $rootCategory = $this->getCategoryRepository()->findOneBy(['parent' => null]);

        if ($rootCategory === null) {
            $message = 'Root category not found';
            throw new \Shopsys\FrameworkBundle\Model\Category\Exception\RootCategoryNotFoundException($message);
        }

        return $rootCategory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getTranslatedAllWithoutBranch(Category $categoryBranch, DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $domainConfig->getLocale());

        return $queryBuilder->andWhere('c.lft < :branchLft OR c.rgt > :branchRgt')
            ->setParameter('branchLft', $categoryBranch->getLft())
            ->setParameter('branchRgt', $categoryBranch->getRgt())
            ->getQuery()
            ->execute();
    }

    public function findById(int $categoryId): ?\Shopsys\FrameworkBundle\Model\Category\Category
    {
        $category = $this->getCategoryRepository()->find($categoryId);
        /* @var $category \Shopsys\FrameworkBundle\Model\Category\Category */

        if ($category !== null && $category->getParent() === null) {
            // Copies logic from getAllQueryBuilder() - excludes root category
            // Query builder is not used to be able to get the category from identity map if it was loaded previously
            return null;
        }

        return $category;
    }

    public function getById(int $categoryId): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        $category = $this->findById($categoryId);

        if ($category === null) {
            $message = 'Category with ID ' . $categoryId . ' not found.';
            throw new \Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException($message);
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

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getPreOrderTreeTraversalForVisibleCategoriesByDomain(int $domainId, string $locale): array
    {
        $queryBuilder = $this->getPreOrderTreeTraversalForAllCategoriesByDomainQueryBuilder($domainId, $locale);

        $queryBuilder->andWhere('cd.visible = TRUE');

        return $queryBuilder->getQuery()->execute();
    }

    protected function getPreOrderTreeTraversalForAllCategoriesByDomainQueryBuilder(int $domainId, string $locale): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        $queryBuilder
            ->join(CategoryDomain::class, 'cd', Join::WITH, 'cd.category = c')
            ->andWhere('c.level >= 1')
            ->andWhere('cd.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->orderBy('c.lft');

        return $queryBuilder;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getTranslatedVisibleSubcategoriesByDomain(Category $parentCategory, DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        $this->addTranslation($queryBuilder, $domainConfig->getLocale());

        $queryBuilder
            ->andWhere('c.parent = :parentCategory')
            ->setParameter('parentCategory', $parentCategory);

        return $queryBuilder->getQuery()->execute();
    }

    protected function addTranslation(QueryBuilder $categoriesQueryBuilder, string $locale): void
    {
        $categoriesQueryBuilder
            ->addSelect('ct')
            ->join('c.translations', 'ct', Join::WITH, 'ct.locale = :locale')
            ->setParameter('locale', $locale);
    }

    public function getPaginationResultForSearchVisible(
        ?string $searchText,
        int $domainId,
        string $locale,
        int $page,
        int $limit
    ): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult {
        $queryBuilder = $this->getVisibleByDomainIdAndSearchTextQueryBuilder($domainId, $locale, $searchText);
        $queryBuilder->orderBy('ct.name');

        $queryPaginator = new QueryPaginator($queryBuilder);

        return $queryPaginator->getResult($page, $limit);
    }

    /**
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getVisibleByDomainIdAndSearchText(int $domainId, string $locale, ?string $searchText): array
    {
        $queryBuilder = $this->getVisibleByDomainIdAndSearchTextQueryBuilder(
            $domainId,
            $locale,
            $searchText
        );

        return $queryBuilder->getQuery()->execute();
    }

    protected function getVisibleByDomainIdAndSearchTextQueryBuilder(
        int $domainId,
        string $locale,
        ?string $searchText
    ): \Doctrine\ORM\QueryBuilder {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId);
        $this->addTranslation($queryBuilder, $locale);
        $this->filterBySearchText($queryBuilder, $searchText);

        return $queryBuilder;
    }

    public function getAllVisibleByDomainIdQueryBuilder(int $domainId): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder()
            ->join(CategoryDomain::class, 'cd', Join::WITH, 'cd.category = c.id')
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

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @return int[]
     */
    public function getListableProductCountsIndexedByCategoryId(
        array $categories,
        PricingGroup $pricingGroup,
        int $domainId
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
                 AND pcd.domainId = :domainId'
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

    protected function filterBySearchText(QueryBuilder $queryBuilder, ?string $searchText): void
    {
        $queryBuilder->andWhere(
            'NORMALIZE(ct.name) LIKE NORMALIZE(:searchText)'
        );
        $queryBuilder->setParameter('searchText', DatabaseSearching::getFullTextLikeSearchString($searchText));
    }

    public function findProductMainCategoryOnDomain(Product $product, int $domainId): ?\Shopsys\FrameworkBundle\Model\Category\Category
    {
        $qb = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->join(
                ProductCategoryDomain::class,
                'pcd',
                Join::WITH,
                'pcd.product = :product
                    AND pcd.category = c
                    AND pcd.domainId = :domainId'
            )
            ->orderBy('c.level DESC, c.lft')
            ->setMaxResults(1);

        $qb->setParameters([
            'domainId' => $domainId,
            'product' => $product,
        ]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getProductMainCategoryOnDomain(Product $product, int $domainId): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        $productMainCategory = $this->findProductMainCategoryOnDomain($product, $domainId);
        if ($productMainCategory === null) {
            throw new \Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException();
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
    public function getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(Product $product, DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $domainId = $domainConfig->getId();
        $locale = $domainConfig->getLocale();
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
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
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
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategoriesWithVisibleChildren(array $categories, int $domainId): array
    {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId);

        $queryBuilder
            ->join(Category::class, 'cc', Join::WITH, 'cc.parent = c')
            ->join(CategoryDomain::class, 'ccd', Join::WITH, 'ccd.category = cc.id')
            ->andWhere('ccd.domainId = :domainId')
            ->andWhere('ccd.visible = TRUE')
            ->andWhere('c IN (:categories)')
            ->setParameter('categories', $categories);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getTranslatedAll(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $domainConfig->getLocale());

        return $queryBuilder->getQuery()
            ->getResult();
    }
}
