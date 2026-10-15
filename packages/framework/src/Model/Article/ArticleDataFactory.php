<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory;

class ArticleDataFactory
{
    public function __construct(
        protected readonly UrlListDataFactory $urlListDataFactory,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly SeoAttributesDataFactory $seoAttributesDataFactory,
        protected readonly ContentDocumentFactory $contentDocumentFactory,
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
        $articleData->image = $this->imageUploadDataFactory->createFromEntityAndType($article);
        $articleData->name = $article->getName();
        $articleData->content = $this->contentDocumentFactory->createFromArray($article->getContentArray() ?? []);
        $articleData->seo = $this->seoAttributesDataFactory->createFromSeoAttributes($article->getSeoAttributes());
        $articleData->domainId = $article->getDomainId();
        $articleData->placement = $article->getPlacement();
        $articleData->hidden = $article->isHidden();
        $articleData->createdAt = $article->getCreatedAt();
        $articleData->publishDate = $article->getPublishDate();
        $articleData->external = $article->isExternal();
        $articleData->type = $article->getType();
        $articleData->url = $article->getUrl();

        $articleData->urls = $this->urlListDataFactory->createForDomain('front_article_detail', $article->getId(), $article->getDomainId());
    }

    protected function fillNew(ArticleData $articleData, int $domainId): void
    {
        $articleData->image = $this->imageUploadDataFactory->create();
        $articleData->content = $this->contentDocumentFactory->createEmpty(ContentProfileEnum::WEB);
        $articleData->domainId = $domainId;
        $articleData->seo = $this->seoAttributesDataFactory->create();
        $articleData->urls = $this->urlListDataFactory->create();
    }
}
