<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

use App\Model\Blog\Article\BlogArticle;
use App\Model\Blog\Article\BlogArticleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class BlogArticleExportRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \App\Model\Blog\Article\BlogArticleRepository
     */
    private BlogArticleRepository $blogArticleRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        BlogArticleRepository $blogArticleRepository,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->blogArticleRepository = $blogArticleRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->em = $em;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $limit
     * @param int $lastProcessedId
     * @return \App\Model\Blog\Article\BlogArticle[]
     */
    public function getVisibleBlogArticlesByDomainIdAndLocale(
        int $domainId,
        string $locale,
        int $limit,
        int $lastProcessedId
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
            ->setParameter('todayDate', (new DateTime())->format('Y-m-d'))
            ->setParameter('domainId', $domainId)
            ->getQuery()->getSingleScalarResult());
    }

    /**
     * @param \App\Model\Blog\Article\BlogArticle $blogArticle
     * @param int $domainId
     * @param string $locale
     * @return array
     */
    public function extractBlogArticle(BlogArticle $blogArticle, int $domainId, string $locale): array
    {
        return [
            'name' => $blogArticle->getName($locale),
            'text' => $blogArticle->getDescription($locale),
            'url' => $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                $domainId,
                'front_blogarticle_detail',
                $blogArticle->getId()
            ),
        ];
    }
}
