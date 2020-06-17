<?php

declare(strict_types=1);

namespace App\Model\Article;

use App\Twig\Cache\TwigCacheFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade as BaseArticleFacade;
use Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository;

/**
 * @property \App\Component\Domain\Domain $domain
 * @method \App\Model\Article\Article|null findById(int $articleId)
 * @method \App\Model\Article\Article getById(int $articleId)
 * @method \App\Model\Article\Article getVisibleById(int $articleId)
 * @method \App\Model\Article\Article[] getVisibleArticlesForPlacementOnCurrentDomain(string $placement)
 * @method \App\Model\Article\Article[] getAllByDomainId(int $domainId)
 */
class ArticleFacade extends BaseArticleFacade
{
    /**
     * @var \App\Twig\Cache\TwigCacheFacade
     */
    private $twigCacheFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface $articleFactory
     * @param \App\Twig\Cache\TwigCacheFacade $twigCacheFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        ArticleRepository $articleRepository,
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade,
        ArticleFactoryInterface $articleFactory,
        TwigCacheFacade $twigCacheFacade
    ) {
        parent::__construct(
            $em,
            $articleRepository,
            $domain,
            $friendlyUrlFacade,
            $articleFactory
        );
        $this->twigCacheFacade = $twigCacheFacade;
    }

    /**
     * @param \App\Model\Article\ArticleData $articleData
     * @return \App\Model\Article\Article
     */
    public function create(ArticleData $articleData): Article
    {
        /** @var \App\Model\Article\Article $article */
        $article = parent::create($articleData);

        if (in_array($article->getPlacement(), $this->getFooterPlacements(), true)) {
            $this->twigCacheFacade->invalidateByKey($article->getPlacement(), $article->getDomainId());
        }

        return $article;
    }

    /**
     * @param int $articleId
     * @param \App\Model\Article\ArticleData $articleData
     * @return \App\Model\Article\Article
     */
    public function edit($articleId, ArticleData $articleData): Article
    {
        /** @var \App\Model\Article\Article $article */
        $article = parent::edit($articleId, $articleData);

        if (in_array($article->getPlacement(), $this->getFooterPlacements(), true)) {
            $this->twigCacheFacade->invalidateByKey($article->getPlacement(), $article->getDomainId());
        }

        return $article;
    }

    /**
     * @return array
     */
    private function getFooterPlacements(): array
    {
        return [
            Article::PLACEMENT_FOOTER_1,
            Article::PLACEMENT_FOOTER_2,
            Article::PLACEMENT_FOOTER_3,
            Article::PLACEMENT_FOOTER_4,
            ];
    }
}
