<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class BlogArticleRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
        protected readonly TransformStringHelper $transformStringHelper,
        protected readonly Localization $localization,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function getBlogArticleRepository(): EntityRepository
    {
        return $this->em->getRepository(BlogArticle::class);
    }

    public function getBlogArticlesByDomainIdQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ba, babcd')
            ->from(BlogArticle::class, 'ba')
            ->join('ba.blogArticleBlogCategoryDomains', 'babcd')
            ->where('babcd.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

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

    public function getBlogArticlesByDomainIdAndLocaleQueryBuilder(int $domainId, string $locale): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ba, bat')
            ->from(BlogArticle::class, 'ba')
            ->join('ba.translations', 'bat', Join::WITH, 'bat.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('ba.createdAt', 'DESC');
    }

    public function getAllBlogArticlesByLocaleQueryBuilder(string $locale): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ba, bat')
            ->from(BlogArticle::class, 'ba')
            ->join('ba.translations', 'bat', Join::WITH, 'bat.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('ba.createdAt', 'DESC');
    }

    public function getQueryBuilderForQuickSearch(
        ?int $domainId,
        QuickSearchFormData $searchData,
        string $locale,
    ): QueryBuilder {
        if ($domainId === null) {
            $queryBuilder = $this->getAllBlogArticlesByLocaleQueryBuilder($locale);
        } else {
            $queryBuilder = $this->getBlogArticlesByDomainIdAndLocaleQueryBuilderIfInBlogCategory($domainId, $locale);
        }

        if ($this->transformStringHelper->emptyToNull($searchData->text) !== null) {
            $uuidCondition = '';

            if (Uuid::isValid($searchData->text)) {
                $uuidCondition = 'ba.uuid = :exactText OR ';
                $queryBuilder->setParameter('exactText', $searchData->text);
            }

            $queryBuilder->andWhere('
                (
                    ' . $uuidCondition . '
                    NORMALIZED(bat.name) LIKE NORMALIZED(:searchData)
                )');
            $queryBuilder->setParameter('searchData', $this->databaseSearchingHelper->getFullTextLikeSearchString($searchData->text));
        }

        return $queryBuilder;
    }

    public function getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder(int $domainId, string $locale): QueryBuilder
    {
        return $this->getBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->join('ba.domains', 'bad', Join::WITH, 'bad.domainId = :domainId')
            ->andWhere('ba.publishDate <= :now')
            ->andWhere('bad.visible = true')
            ->andWhere('ba.hidden = false')
            ->setParameter('now', $this->clock->now())
            ->setParameter('domainId', $domainId);
    }

    public function getAllBlogArticlesCountByDomainId(int $domainId): int
    {
        return (int)($this->getBlogArticlesByDomainIdQueryBuilder($domainId)
            ->select('COUNT(ba)')
            ->getQuery()->getSingleScalarResult());
    }

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
     * @return int[]
     */
    public function getAllIdsByDomainId(int $domainId): array
    {
        $result = $this->getBlogArticlesByDomainIdQueryBuilder($domainId)
            ->select('ba.id')
            ->getQuery()
            ->execute();

        return array_column($result, 'id');
    }

    /**
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
