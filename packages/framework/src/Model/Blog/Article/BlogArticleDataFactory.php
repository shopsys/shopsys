<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\GrapesJs\EnsureCorrectGrapesJsFormatHelper;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory;

class BlogArticleDataFactory
{
    public function __construct(
        protected readonly UrlListDataFactory $urlListDataFactory,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly EnsureCorrectGrapesJsFormatHelper $ensureCorrectGrapesJsFormatHelper,
        protected readonly SeoAttributesDataFactory $seoAttributesDataFactory,
    ) {
    }

    public function createFromBlogArticle(BlogArticle $blogArticle): BlogArticleData
    {
        $blogArticleData = $this->createInstance();
        $this->fillFromBlogArticle($blogArticleData, $blogArticle);

        return $blogArticleData;
    }

    public function create(): BlogArticleData
    {
        $blogArticleData = $this->createInstance();
        $this->fillNew($blogArticleData);

        return $blogArticleData;
    }

    protected function fillNew(BlogArticleData $blogArticleData): void
    {
        $blogArticleData->image = $this->imageUploadDataFactory->create();

        foreach ($this->domain->getAllIds() as $domainId) {
            $blogArticleData->seo[$domainId] = $this->seoAttributesDataFactory->create();
            $blogArticleData->urls[$domainId] = $this->urlListDataFactory->create();
            $blogArticleData->enabled[$domainId] = true;
            $blogArticleData->statuses[$domainId] = BlogArticleStatusEnum::STATUS_DRAFT;
            $blogArticleData->publishDates[$domainId] = null;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogArticleData->names[$locale] = null;
            $blogArticleData->descriptions[$locale] = null;
            $blogArticleData->perexes[$locale] = null;
        }
    }

    protected function fillFromBlogArticle(BlogArticleData $blogArticleData, BlogArticle $blogArticle): void
    {
        $blogArticleData->names = $blogArticle->getNames();

        foreach ($blogArticle->getDescriptions() as $locale => $description) {
            $blogArticleData->descriptions[$locale] = $this->ensureCorrectGrapesJsFormatHelper->ensureStringIsInCorrectGrapesJsFormat($description, $locale);
        }

        $blogArticleData->perexes = $blogArticle->getPerexes();
        $blogArticleData->visibleOnHomepage = $blogArticle->isVisibleOnHomepage();
        $blogArticleData->createdAt = $blogArticle->getCreatedAt();
        $blogArticleData->blogCategoriesByDomainId = $blogArticle->getBlogCategoriesIndexedByDomainId();
        $blogArticleData->uuid = $blogArticle->getUuid();
        $blogArticleData->blogArticleAuthor = $blogArticle->getBlogArticleAuthor();

        $blogArticleData->image = $this->imageUploadDataFactory->createFromEntityAndType($blogArticle);

        $blogArticleData->urls = $this->urlListDataFactory->createForAllDomainsIndexedByDomainId('front_blogarticle_detail', $blogArticle->getId());

        foreach ($this->domain->getAllIds() as $domainId) {
            $blogArticleData->seo[$domainId] = $this->seoAttributesDataFactory->createFromSeoAttributes(
                $blogArticle->getSeoAttributes($domainId),
            );
            $blogArticleData->statuses[$domainId] = $blogArticle->getStatus($domainId);
            $blogArticleData->publishDates[$domainId] = $blogArticle->getPublishDate($domainId);
        }
    }

    protected function createInstance(): BlogArticleData
    {
        return new BlogArticleData();
    }
}
