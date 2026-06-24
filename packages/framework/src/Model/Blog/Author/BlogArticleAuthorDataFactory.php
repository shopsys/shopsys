<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Author;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class BlogArticleAuthorDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    protected function createInstance(): BlogArticleAuthorData
    {
        return new BlogArticleAuthorData();
    }

    public function create(): BlogArticleAuthorData
    {
        $blogArticleAuthorData = $this->createInstance();
        $this->fillNew($blogArticleAuthorData);

        return $blogArticleAuthorData;
    }

    protected function fillNew(BlogArticleAuthorData $blogArticleAuthorData): void
    {
        $blogArticleAuthorData->image = $this->imageUploadDataFactory->create();

        foreach ($this->domain->getAllLocales() as $locale) {
            $blogArticleAuthorData->jobTitles[$locale] = null;
            $blogArticleAuthorData->descriptions[$locale] = null;
        }
    }

    public function createFromBlogArticleAuthor(BlogArticleAuthor $blogArticleAuthor): BlogArticleAuthorData
    {
        $blogArticleAuthorData = $this->createInstance();
        $this->fillFromBlogArticleAuthor($blogArticleAuthorData, $blogArticleAuthor);

        return $blogArticleAuthorData;
    }

    protected function fillFromBlogArticleAuthor(
        BlogArticleAuthorData $blogArticleAuthorData,
        BlogArticleAuthor $blogArticleAuthor,
    ): void {
        $blogArticleAuthorData->name = $blogArticleAuthor->getName();

        foreach ($blogArticleAuthor->getTranslations() as $translation) {
            $blogArticleAuthorData->jobTitles[$translation->getLocale()] = $translation->getJobTitle();
            $blogArticleAuthorData->descriptions[$translation->getLocale()] = $translation->getDescription();
        }

        $blogArticleAuthorData->image = $this->imageUploadDataFactory->createFromEntityAndType($blogArticleAuthor);
    }
}
