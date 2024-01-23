<?php

declare(strict_types=1);

namespace App\Model\Sitemap;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapRepository as BaseSitemapRepository;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @method __construct(\App\Model\Product\ProductRepository $productRepository, \App\Model\Category\CategoryRepository $categoryRepository, \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository, \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository $blogArticleRepository)
 */
class SitemapRepository extends BaseSitemapRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapItem[]
     */
    public function getSitemapItemsForVisibleCategorySeoMix(DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->categoryRepository->getAllVisibleByDomainIdQueryBuilder($domainConfig->getId());
        $queryBuilder
            ->select('fu.slug, fu.entityId')
            ->join(ReadyCategorySeoMix::class, 'rcsm', Join::WITH, 'rcsm.category = c AND rcsm.domainId = :domainId')
            ->join(
                FriendlyUrl::class,
                'fu',
                Join::WITH,
                'fu.routeName = :categorySeoMixRouteName
                AND fu.entityId = rcsm
                AND fu.domainId = :domainId
                AND fu.main = TRUE',
            )
            ->setParameter('categorySeoMixRouteName', 'front_category_seo')
            ->setParameter('domainId', $domainConfig->getId());

        return $this->getSitemapItemsFromQueryBuilderWithSlugField($queryBuilder);
    }
}
