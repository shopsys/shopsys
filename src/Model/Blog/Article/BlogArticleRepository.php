<?php

declare(strict_types = 1);

namespace App\Model\Blog\Article;

use App\Model\Blog\Category\BlogCategory;
use App\Model\Product\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;

class BlogArticleRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getBlogArticleRepository(): EntityRepository
    {
        return $this->em->getRepository(BlogArticle::class);
    }

    /**
     * @param int $blogArticleId
     * @return \App\Model\Blog\Article\BlogArticle|null
     */
    public function findById(int $blogArticleId): ?BlogArticle
    {
        return $this->getBlogArticleRepository()->find($blogArticleId);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBlogArticlesByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ba, babcd')
            ->from(BlogArticle::class, 'ba')
            ->join('ba.blogArticleBlogCategoryDomains', 'babcd')
            ->where('babcd.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBlogArticlesByDomainIdAndLocaleQueryBuilder(int $domainId, string $locale): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ba, bat')
            ->from(BlogArticle::class, 'ba')
            ->join('ba.translations', 'bat', Join::WITH, 'bat.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('ba.createdAt', 'DESC');
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param int $domainId
     */
    private function addBlogArticleBlogCategoryDomainsToQueryBuilder(QueryBuilder $queryBuilder, int $domainId): void
    {
        $queryBuilder
            ->addSelect('babcd')
            ->join('ba.blogArticleBlogCategoryDomains', 'babcd', Join::WITH, 'babcd.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllBlogArticlesByLocaleQueryBuilder(string $locale): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ba, bat')
            ->from(BlogArticle::class, 'ba')
            ->join('ba.translations', 'bat', Join::WITH, 'bat.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('ba.createdAt', 'DESC');
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder(int $domainId, string $locale): QueryBuilder
    {
        return $this->getBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->join('ba.domains', 'bad', Join::WITH, 'bad.domainId = :domainId')
            ->andWhere('ba.publishDate <= :todayDate')
            ->andWhere('bad.visible = true')
            ->setParameter('todayDate', (new DateTime())->format('Y-m-d'))
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllBlogArticlesCountByDomainId(int $domainId): int
    {
        return (int)($this->getBlogArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(ba)')
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @param int $blogArticleId
     * @return \App\Model\Blog\Article\BlogArticle
     */
    public function getById(int $blogArticleId): BlogArticle
    {
        $blogArticle = $this->getBlogArticleRepository()->find($blogArticleId);

        if ($blogArticle === null) {
            $message = 'Blog article with ID ' . $blogArticleId . ' not found';
            throw new \App\Model\Blog\Article\Exception\BlogArticleNotFoundException($message);
        }

        return $blogArticle;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int $blogArticleId
     * @return \App\Model\Blog\Article\BlogArticle
     */
    public function getVisibleOnDomainById(DomainConfig $domainConfig, int $blogArticleId): BlogArticle
    {
        $blogArticleQueryBuilder = $this->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainConfig->getId(), $domainConfig->getLocale());

        $this->addBlogArticleBlogCategoryDomainsToQueryBuilder($blogArticleQueryBuilder, $domainConfig->getId());

        $blogArticle = $blogArticleQueryBuilder
            ->andWhere('ba.id = :blogArticleId')
            ->setParameter('blogArticleId', $blogArticleId)
            ->getQuery()->getOneOrNullResult();

        if ($blogArticle === null) {
            $message = 'Article with ID ' . $blogArticleId . ' not found';
            throw new \App\Model\Blog\Article\Exception\BlogArticleNotFoundException($message);
        }
        return $blogArticle;
    }

    /**
     * @param int $domainId
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getBlogArticlesByDomainIdQueryBuilder($domainId)
            ->orderBy('ba.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $limit
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getHomepageBlogArticlesByDomainId(int $domainId, string $locale, int $limit): array
    {
        return $this->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->andWhere('ba.visibleOnHomepage = true')
            ->setMaxResults($limit)
            ->orderBy('ba.publishDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     * @param string $locale
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResultForListableInBlogCategory(
        BlogCategory $blogCategory,
        int $domainId,
        string $locale,
        int $page,
        int $limit
    ): PaginationResult {
        $queryBuilder = $this->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale);
        $this->addBlogArticleBlogCategoryDomainsToQueryBuilder($queryBuilder, $domainId);
        $queryBuilder->andWhere('babcd.blogCategory = :blogCategory');
        $queryBuilder->setParameter('blogCategory', $blogCategory);

        $queryPaginator = new QueryPaginator($queryBuilder);

        return $queryPaginator->getResult($page, $limit);
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticle $blogArticle
     * @param int $domainId
     * @return \App\Model\Blog\Category\BlogCategory
     */
    public function findBlogArticleMainCategoryOnDomain(BlogArticle $blogArticle, int $domainId): ?BlogCategory
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('babcd')
            ->from(BlogArticleBlogCategoryDomain::class, 'babcd')
            ->join('babcd.blogCategory', 'bc')
            ->andWhere('babcd.domainId = :domainId')
            ->andWhere('babcd.blogArticle = :blogArticle')
            ->orderBy('bc.level DESC, bc.lft')
            ->setMaxResults(1);

        $queryBuilder->setParameters([
            'domainId' => $domainId,
            'blogArticle' => $blogArticle,
        ]);

        $blogArticleBlogCategoryDomain = $queryBuilder->getQuery()->getOneOrNullResult();

        return $blogArticleBlogCategoryDomain === null ? null : $blogArticleBlogCategoryDomain->getBlogCategory();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param string $locale
     * @param int $limit
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getVisibleByProduct(Product $product, int $domainId, string $locale, int $limit): array
    {
        return $this->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->innerJoin('ba.products', 'p')
            ->andWhere('p = :product')
            ->setParameter('product', $product)
            ->setMaxResults($limit)
            ->orderBy('ba.publishDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getByProduct(Product $product, string $locale): array
    {
        return $this->getAllBlogArticlesByLocaleQueryBuilder($locale)
            ->innerJoin('ba.products', 'p')
            ->andWhere('p = :product')
            ->setParameter('product', $product)
            ->orderBy('bat.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return string[]
     */
    public function getAllBlogArticlesNamesIndexedByIdByDomainId(int $domainId, string $locale): array
    {
        $queryBuilder = $this->getBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale);

        $rows = $queryBuilder->select('ba.id, bat.name')->getQuery()->getScalarResult();

        $blogArticlesNameById = [];
        foreach ($rows as $row) {
            $blogArticlesNameById[$row['id']] = $row['name'];
        }

        return $blogArticlesNameById;
    }
}
