<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\GrapesJs\GrapesJsParser;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class BlogArticleExportRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade $breadcrumbFacade
     * @param \Shopsys\FrameworkBundle\Component\GrapesJs\GrapesJsParser $grapesJsParser
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BreadcrumbFacade $breadcrumbFacade,
        protected readonly GrapesJsParser $grapesJsParser,
        protected readonly ImageFacade $imageFacade,
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
    ) {
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $limit
     * @param int $lastProcessedId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]
     */
    public function getVisibleBlogArticlesByDomainIdAndLocale(
        int $domainId,
        string $locale,
        int $limit,
        int $lastProcessedId,
    ): array {
        return $this->blogArticleRepository->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->andWhere('ba.id > :lastProcessedId')
            ->setParameter('lastProcessedId', $lastProcessedId)
            ->setMaxResults($limit)
            ->orderBy('ba.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int[] $blogArticleIds
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle[]
     */
    public function getVisibleBlogArticlesByDomainIdAndLocaleAndBlogArticleIds(
        int $domainId,
        string $locale,
        array $blogArticleIds,
    ): array {
        return $this->blogArticleRepository->getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder($domainId, $locale)
            ->andWhere('ba.id IN (:blogArticleIds)')
            ->setParameter('blogArticleIds', $blogArticleIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return int
     */
    public function getVisibleBlogArticlesCountByDomainIdAndLocale(int $domainId, string $locale): int
    {
        return (int)($this->em->createQueryBuilder()
            ->select('COUNT(ba)')
            ->from(BlogArticle::class, 'ba')
            ->join('ba.translations', 'bat', Join::WITH, 'bat.locale = :locale')
            ->setParameter('locale', $locale)
            ->join('ba.domains', 'bad', Join::WITH, 'bad.domainId = :domainId')
            ->andWhere('ba.publishDate <= :todayDate')
            ->andWhere('bad.visible = true')
            ->andWhere('ba.hidden = false')
            ->setParameter('todayDate', (new DateTime())->format('Y-m-d H:i:s'))
            ->setParameter('domainId', $domainId)
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle $blogArticle
     * @param int $domainId
     * @param string $locale
     * @return array
     */
    public function extractBlogArticle(BlogArticle $blogArticle, int $domainId, string $locale): array
    {
        $blogArticleCategories = $blogArticle->getBlogCategoriesIndexedByDomainId()[$domainId];
        $mainFriendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($domainId, 'front_blogarticle_detail', $blogArticle->getId());
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        try {
            $imageUrl = $this->imageFacade->getImageUrl($domainConfig, $blogArticle);
        } catch (ImageNotFoundException $exception) {
            $imageUrl = null;
        }

        return [
            'name' => $blogArticle->getName($locale),
            'text' => $this->grapesJsParser->parse($blogArticle->getDescription($locale)),
            'url' => $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($mainFriendlyUrl),
            'uuid' => $blogArticle->getUuid(),
            'createdAt' => $blogArticle->getCreatedAt()->format('Y-m-d H:i:s'),
            'visibleOnHomepage' => $blogArticle->isVisibleOnHomepage(),
            'publishedAt' => $blogArticle->getPublishDate()->format('Y-m-d'),
            'perex' => $blogArticle->getPerex($locale),
            'seoTitle' => $blogArticle->getSeoTitle($domainId),
            'seoMetaDescription' => $blogArticle->getSeoMetaDescription($domainId),
            'seoH1' => $blogArticle->getSeoH1($domainId),
            'slug' => $this->friendlyUrlFacade->getAllSlugsByRouteNameAndEntityId($domainId, 'front_blogarticle_detail', $blogArticle->getId()),
            'categories' => array_map(fn (BlogCategory $blogCategory) => $blogCategory->getId(), $blogArticleCategories),
            'mainSlug' => $mainFriendlyUrl->getSlug(),
            'breadcrumb' => $this->breadcrumbFacade->getBreadcrumbOnDomain($blogArticle->getId(), 'front_blogarticle_detail', $domainId, $locale),
            'imageUrl' => $imageUrl,
            'hreflangLinks' => $this->hreflangLinksFacade->getForBlogArticle($blogArticle, $domainId),
        ];
    }
}
