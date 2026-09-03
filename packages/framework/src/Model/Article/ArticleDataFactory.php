<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\GrapesJs\EnsureCorrectGrapesJsFormatHelper;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory;

class ArticleDataFactory
{
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly Domain $domain,
        protected readonly EnsureCorrectGrapesJsFormatHelper $ensureCorrectGrapesJsFormatHelper,
        protected readonly SeoAttributesDataFactory $seoAttributesDataFactory,
    ) {
    }

    protected function createInstance(): ArticleData
    {
        return new ArticleData();
    }

    public function createFromArticle(Article $article): ArticleData
    {
        $articleData = $this->createInstance();
        $this->fillFromArticle($articleData, $article);

        return $articleData;
    }

    public function create(int $domainId): ArticleData
    {
        $articleData = $this->createInstance();
        $this->fillNew($articleData, $domainId);

        return $articleData;
    }

    protected function fillFromArticle(ArticleData $articleData, Article $article): void
    {
        $articleData->name = $article->getName();
        $articleData->text = $this->ensureCorrectGrapesJsFormatHelper->ensureStringIsInCorrectGrapesJsFormat(
            $article->getText(),
            $this->domain->getDomainConfigById($article->getDomainId())->getLocale(),
        );
        $articleData->seo = $this->seoAttributesDataFactory->createFromSeoAttributes($article->getSeoAttributes());
        $articleData->domainId = $article->getDomainId();
        $articleData->placement = $article->getPlacement();
        $articleData->hidden = $article->isHidden();
        $articleData->createdAt = $article->getCreatedAt();
        $articleData->external = $article->isExternal();
        $articleData->type = $article->getType();
        $articleData->url = $article->getUrl();

        $articleData->urls->mainFriendlyUrlsByDomainId[$article->getDomainId()] =
            $this->friendlyUrlFacade->findMainFriendlyUrl(
                $article->getDomainId(),
                'front_article_detail',
                $article->getId(),
            );
    }

    protected function fillNew(ArticleData $articleData, int $domainId): void
    {
        $articleData->domainId = $domainId;
        $articleData->seo = $this->seoAttributesDataFactory->create();
    }
}
