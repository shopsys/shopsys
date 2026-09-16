<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory;

class BlogCategoryDataFactory
{
    public function __construct(
        protected readonly UrlListDataFactory $urlListDataFactory,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly SeoAttributesDataFactory $seoAttributesDataFactory,
    ) {
    }

    public function createFromBlogCategory(BlogCategory $blogCategory): BlogCategoryData
    {
        $blogCategoryData = $this->createInstance();
        $this->fillFromBlogCategory($blogCategoryData, $blogCategory);

        return $blogCategoryData;
    }

    public function create(): BlogCategoryData
    {
        $blogCategoryData = $this->createInstance();
        $this->fillNew($blogCategoryData);

        return $blogCategoryData;
    }

    protected function fillNew(BlogCategoryData $blogCategoryData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $blogCategoryData->seo[$domainId] = $this->seoAttributesDataFactory->create();
            $blogCategoryData->urls[$domainId] = $this->urlListDataFactory->create();
            $blogCategoryData->enabled[$domainId] = true;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogCategoryData->names[$locale] = null;
            $blogCategoryData->descriptions[$locale] = null;
        }

        $blogCategoryData->image = $this->imageUploadDataFactory->create();
    }

    protected function fillFromBlogCategory(BlogCategoryData $blogCategoryData, BlogCategory $blogCategory): void
    {
        $blogCategoryData->names = $blogCategory->getNames();
        $blogCategoryData->descriptions = $blogCategory->getDescriptions();
        $blogCategoryData->parent = $blogCategory->getParent();
        $blogCategoryData->uuid = $blogCategory->getUuid();

        $blogCategoryData->image = $this->imageUploadDataFactory->createFromEntityAndType($blogCategory);

        $blogCategoryData->urls = $this->urlListDataFactory->createForAllDomainsIndexedByDomainId('front_blogcategory_detail', $blogCategory->getId());

        foreach ($this->domain->getAllIds() as $domainId) {
            $blogCategoryData->seo[$domainId] = $this->seoAttributesDataFactory->createFromSeoAttributes(
                $blogCategory->getSeoAttributes($domainId),
            );
            $blogCategoryData->enabled[$domainId] = $blogCategory->isEnabled($domainId);
        }
    }

    protected function createInstance(): BlogCategoryData
    {
        return new BlogCategoryData();
    }
}
