<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class BlogCategoryDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData
     */
    public function createFromBlogCategory(BlogCategory $blogCategory): BlogCategoryData
    {
        $blogCategoryData = $this->createInstance();
        $this->fillFromBlogCategory($blogCategoryData, $blogCategory);

        return $blogCategoryData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData
     */
    public function create(): BlogCategoryData
    {
        $blogCategoryData = $this->createInstance();
        $this->fillNew($blogCategoryData);

        return $blogCategoryData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData $blogCategoryData
     */
    protected function fillNew(BlogCategoryData $blogCategoryData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $blogCategoryData->seoMetaDescriptions[$domainId] = null;
            $blogCategoryData->seoTitles[$domainId] = null;
            $blogCategoryData->seoH1s[$domainId] = null;
            $blogCategoryData->enabled[$domainId] = true;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogCategoryData->names[$locale] = null;
            $blogCategoryData->descriptions[$locale] = null;
        }

        $blogCategoryData->image = $this->imageUploadDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData $blogCategoryData
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     */
    protected function fillFromBlogCategory(BlogCategoryData $blogCategoryData, BlogCategory $blogCategory): void
    {
        $blogCategoryData->names = $blogCategory->getNames();
        $blogCategoryData->descriptions = $blogCategory->getDescriptions();
        $blogCategoryData->parent = $blogCategory->getParent();
        $blogCategoryData->uuid = $blogCategory->getUuid();

        $blogCategoryData->image = $this->imageUploadDataFactory->createFromEntityAndType($blogCategory);

        foreach ($this->domain->getAllIds() as $domainId) {
            $blogCategoryData->seoMetaDescriptions[$domainId] = $blogCategory->getSeoMetaDescription($domainId);
            $blogCategoryData->seoTitles[$domainId] = $blogCategory->getSeoTitle($domainId);
            $blogCategoryData->seoH1s[$domainId] = $blogCategory->getSeoH1($domainId);
            $blogCategoryData->enabled[$domainId] = $blogCategory->isEnabled($domainId);

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($domainId, 'front_blogcategory_detail', $blogCategory->getId());
            $blogCategoryData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData
     */
    protected function createInstance(): BlogCategoryData
    {
        return new BlogCategoryData();
    }
}
