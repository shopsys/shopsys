<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleBlogCategoryDomain;
use Shopsys\FrameworkBundle\Model\Blog\Category\Exception\BlogCategoryNotFoundException;

class BlogCategoryRepository extends NestedTreeRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
    ) {
        $classMetadata = $this->em->getClassMetadata(BlogCategory::class);

        parent::__construct($this->em, $classMetadata);
    }

    protected function getBlogCategoryRepository(): EntityRepository
    {
        return $this->em->getRepository(BlogCategory::class);
    }

    protected function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getBlogCategoryRepository()
            ->createQueryBuilder('bc')
            ->orderBy('bc.lft');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $selectedBlogCategories
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getAllBlogCategoriesOfCollapsedTree(array $selectedBlogCategories): array
    {
        $openedParentsQueryBuilder = $this->getBlogCategoryRepository()
            ->createQueryBuilder('bc')
            ->select('bc.id')
            ->where('bc.parent IS NULL');

        foreach ($selectedBlogCategories as $selectedBlogCategory) {
            $where = sprintf('bc.lft < %d AND bc.rgt > %d', $selectedBlogCategory->getLft(), $selectedBlogCategory->getRgt());
            $openedParentsQueryBuilder->orWhere($where);
        }

        $openedParentIds = array_column($openedParentsQueryBuilder->getQuery()->getScalarResult(), 'id');

        return $this->getAllQueryBuilder()
            ->select('bc, bcd, bct')
            ->join('bc.domains', 'bcd')
            ->join('bc.translations', 'bct')
            ->where('bc.parent IN (:openedParentIds) OR bc.parent IS NULL')
            ->setParameter('openedParentIds', $openedParentIds)
            ->getQuery()
            ->getResult();
    }

    public function getRootBlogCategory(): BlogCategory
    {
        return $this->getBlogCategoryRepository()->findOneBy(['parent' => null]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getTranslatedAllWithoutBranch(BlogCategory $blogCategoryBranch, string $locale): array
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        return $queryBuilder->andWhere('bc.lft < :branchLft OR bc.rgt > :branchRgt')
            ->setParameter('branchLft', $blogCategoryBranch->getLft())
            ->setParameter('branchRgt', $blogCategoryBranch->getRgt())
            ->getQuery()
            ->getResult();
    }

    public function findById(int $blogCategoryId): ?BlogCategory
    {
        /** @var \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory */
        $blogCategory = $this->getBlogCategoryRepository()->find($blogCategoryId);

        return $blogCategory;
    }

    public function getById(int $blogCategoryId): BlogCategory
    {
        $blogCategory = $this->findById($blogCategoryId);

        if ($blogCategory === null) {
            $message = 'BlogCategory with ID ' . $blogCategoryId . ' not found.';

            throw new BlogCategoryNotFoundException($message);
        }

        return $blogCategory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getPreOrderTreeTraversalForAllBlogCategories(string $locale): array
    {
        $queryBuilder = $this->getPreOrderTreeTraversalForAllBlogCategoriesQueryBuilder($locale);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getPreOrderTreeTraversalForVisibleBlogCategoriesOnDomain(int $domainId, string $locale): array
    {
        return $this->getPreOrderTreeTraversalForVisibleBlogCategoriesOnDomainQueryBuilder($domainId, $locale)
            ->getQuery()
            ->getResult();
    }

    protected function getPreOrderTreeTraversalForVisibleBlogCategoriesOnDomainQueryBuilder(
        int $domainId,
        string $locale,
    ): QueryBuilder {
        return $this->getPreOrderTreeTraversalForAllBlogCategoriesQueryBuilder($locale)
            ->join('bc.domains', 'bcd')
            ->andWhere('bcd.visible = TRUE')
            ->andWhere('bcd.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    protected function getPreOrderTreeTraversalForAllBlogCategoriesQueryBuilder(string $locale): QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        $queryBuilder
            ->orderBy('bc.lft');

        return $queryBuilder;
    }

    protected function addTranslation(QueryBuilder $blogCategoriesQueryBuilder, string $locale): void
    {
        $blogCategoriesQueryBuilder
            ->addSelect('bct')
            ->join('bc.translations', 'bct', Join::WITH, 'bct.locale = :locale')
            ->setParameter('locale', $locale);
    }

    public function getAllVisibleByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder()
            ->join('bc.domains', 'bcd')
            ->andWhere('bcd.domainId = :domainId')
            ->andWhere('bcd.visible = TRUE');

        $queryBuilder->setParameter('domainId', $domainId);

        return $queryBuilder;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getAllVisibleChildrenWithRootByDomainId(int $domainId): array
    {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->andWhere('bc.parent IS NULL');

        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();
        $this->addTranslation($queryBuilder, $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getAllVisibleChildrenByBlogCategoryAndDomainId(BlogCategory $blogCategory, int $domainId): array
    {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->andWhere('bc.parent = :blogCategory')
            ->setParameter('blogCategory', $blogCategory);

        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();
        $this->addTranslation($queryBuilder, $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findBlogArticleMainBlogCategoryOnDomain(BlogArticle $blogArticle, int $domainId): ?BlogCategory
    {
        $qb = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->join(
                BlogArticleBlogCategoryDomain::class,
                'babcd',
                Join::WITH,
                'babcd.blogArticle = :blogArticle
                    AND babcd.blogCategory = bc
                    AND babcd.domainId = :domainId',
            )
            ->orderBy('bc.level DESC, bc.lft')
            ->setMaxResults(1);

        $qb->setParameter('domainId', $domainId)
            ->setParameter('blogArticle', $blogArticle);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getBlogArticleMainBlogCategoryOnDomain(BlogArticle $product, int $domainId): BlogCategory
    {
        $blogArticleMainBlogCategory = $this->findBlogArticleMainBlogCategoryOnDomain($product, $domainId);

        if ($blogArticleMainBlogCategory === null) {
            throw new BlogCategoryNotFoundException();
        }

        return $blogArticleMainBlogCategory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getVisibleBlogCategoriesInPathFromRootOnDomain(BlogCategory $blogCategory, int $domainId): array
    {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->andWhere('bc.lft <= :lft')->setParameter('lft', $blogCategory->getLft())
            ->andWhere('bc.rgt >= :rgt')->setParameter('rgt', $blogCategory->getRgt())
            ->orderBy('bc.lft');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getAllByLocale(string $locale): array
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        return $queryBuilder->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $blogCategoryIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory>
     */
    public function getByIds(array $blogCategoryIds): array
    {
        return $this->getAllQueryBuilder()
            ->andWhere('bc.id IN (:blogCategoryIds)')
            ->setParameter('blogCategoryIds', $blogCategoryIds)
            ->getQuery()
            ->getResult();
    }

    public function getVisibleByUuid(int $domainId, string $uuid): BlogCategory
    {
        $blogCategory = $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->andWhere('bc.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()->getOneOrNullResult();

        if ($blogCategory === null) {
            throw new BlogCategoryNotFoundException(sprintf('No visible blog category was found by UUID "%s"', $uuid));
        }

        return $blogCategory;
    }

    /**
     * @param int[] $blogCategoryIds
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    public function getVisibleByIds(int $domainId, array $blogCategoryIds): array
    {
        return $this->getAllVisibleByDomainIdQueryBuilder($domainId)
            ->andWhere('bc.id IN (:blogCategoryIds)')
            ->setParameter('blogCategoryIds', $blogCategoryIds)
            ->getQuery()
            ->getResult();
    }

    public function findVisibleMainBlogCategoryIdOnDomain(int $domainId): ?int
    {
        try {
            return $this->getAllVisibleByDomainIdQueryBuilder($domainId)
                ->select('bc.id')
                ->getQuery()
                ->setMaxResults(1)
                ->getSingleScalarResult();
        } catch (NoResultException) {
            return null;
        }
    }

    public function findVisibleMainBlogCategoryOnDomain(int $domainId): ?BlogCategory
    {
        $queryBuilder = $this->getAllVisibleByDomainIdQueryBuilder($domainId);
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();
        $this->addTranslation($queryBuilder, $locale);

        return $queryBuilder
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, string>
     */
    public function getVisibilityOfBlogCategoriesIndexedById(int $domainCount): array
    {
        $visibilityOfBlogCategoriesIndexedById = [];

        $result = $this->getAllQueryBuilder()
            ->select('bc.id, COUNT(bcd.domainId) AS domainCount')
            ->join(BlogCategoryDomain::class, 'bcd', Join::WITH, 'bcd.blogCategory = bc')
            ->andWhere('bcd.visible = TRUE')
            ->groupBy('bc.id')
            ->getQuery()
            ->getArrayResult();

        foreach ($result as $row) {
            $visibilityOfBlogCategoriesIndexedById[$row['id']] = $row['domainCount'] === $domainCount ? 'all' : 'partial';
        }

        return $visibilityOfBlogCategoriesIndexedById;
    }
}
