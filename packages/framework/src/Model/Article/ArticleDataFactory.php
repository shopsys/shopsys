<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class ArticleDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\ArticleData
     */
    protected function createInstance(): ArticleData
    {
        return new ArticleData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article $article
     * @return \Shopsys\FrameworkBundle\Model\Article\ArticleData
     */
    public function createFromArticle(Article $article): ArticleData
    {
        $articleData = $this->createInstance();
        $this->fillFromArticle($articleData, $article);

        return $articleData;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\ArticleData
     */
    public function create(int $domainId): ArticleData
    {
        $articleData = $this->createInstance();
        $this->fillNew($articleData, $domainId);

        return $articleData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     * @param \Shopsys\FrameworkBundle\Model\Article\Article $article
     */
    protected function fillFromArticle(ArticleData $articleData, Article $article)
    {
        $articleData->name = $article->getName();
        $articleData->text = $article->getText();
        $articleData->seoTitle = $article->getSeoTitle();
        $articleData->seoMetaDescription = $article->getSeoMetaDescription();
        $articleData->domainId = $article->getDomainId();
        $articleData->placement = $article->getPlacement();
        $articleData->hidden = $article->isHidden();
        $articleData->seoH1 = $article->getSeoH1();
        $articleData->createdAt = $article->getCreatedAt();
        $articleData->external = $article->isExternal();
        $articleData->type = $article->getType();
        $articleData->url = $article->getUrl();

        foreach ($this->domain->getAll() as $domainConfig) {
            $articleData->urls->mainFriendlyUrlsByDomainId[$domainConfig->getId()] =
                $this->friendlyUrlFacade->findMainFriendlyUrl(
                    $domainConfig->getId(),
                    'front_article_detail',
                    $article->getId(),
                );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleData $articleData
     * @param int $domainId
     */
    protected function fillNew(ArticleData $articleData, int $domainId)
    {
        $articleData->domainId = $domainId;
    }
}
