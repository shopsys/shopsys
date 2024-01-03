<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class BlogArticleRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getBlogArticleRepository(): EntityRepository
    {
        return $this->em->getRepository(BlogArticle::class);
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
    public function getBlogArticlesByDomainIdAndLocaleQueryBuilderIfInBlogCategory(
        int $domainId,
        string $locale,
    ): QueryBuilder {
        $queryBuilder = $this->getBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale);
        $subquery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(BlogCategory::class, 'bc')
            ->join('ba.blogArticleBlogCategoryDomains', 'babcd', Join::WITH, 'bc = babcd.blogCategory AND babcd.domainId = :domainId');
        $queryBuilder->andWhere('EXISTS(' . $subquery->getDQL() . ')')
            ->setParameter('domainId', $domainId);

        return $queryBuilder;
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
            ->andWhere('ba.hidden = false')
            ->setParameter('todayDate', (new DateTime())->format('Y-m-d H:i:s'))
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
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle
     */
    public function getById(int $blogArticleId): BlogArticle
    {
        $blogArticle = $this->getBlogArticleRepository()->find($blogArticleId);

        if ($blogArticle === null) {
            $message = 'Blog article with ID ' . $blogArticleId . ' not found';

            throw new BlogArticleNotFoundException($message);
        }

        return $blogArticle;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getBlogArticlesByDomainIdQueryBuilder($domainId)
            ->orderBy('ba.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param int $domainId
     * @param string $locale
     * @return int[]
     */
    public function getBlogArticleIdsByCategory(BlogCategory $blogCategory, int $domainId, string $locale): array
    {
        $result = $this->getBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->resetDQLPart('select')
            ->select('ba.id')
            ->join('ba.blogArticleBlogCategoryDomains', 'babcd')
            ->where('babcd.blogCategory = :blogCategory')
            ->andWhere('babcd.domainId = :domainId')
            ->setParameter('blogCategory', $blogCategory)
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->execute();

        return array_column($result, 'id');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]
     */
    public function getAllVisibleOnDomain(DomainConfig $domainConfig): array
    {
        $blogArticleQueryBuilder = $this->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainConfig->getId(), $domainConfig->getLocale());

        return $blogArticleQueryBuilder
            ->getQuery()
            ->getResult();
    }
}
