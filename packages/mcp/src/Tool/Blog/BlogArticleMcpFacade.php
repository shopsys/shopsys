<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool\Blog;

use Mcp\Exception\ToolCallException;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListDataFactory;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleData;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;

class BlogArticleMcpFacade
{
    protected const string ROUTE_NAME = 'front_blogarticle_detail';

    public function __construct(
        protected readonly BlogArticleFacade $blogArticleFacade,
        protected readonly BlogArticleDataFactory $blogArticleDataFactory,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly UrlListDataFactory $urlListDataFactory,
    ) {
    }

    public function edit(EditBlogArticleArguments $arguments): BlogArticle
    {
        $blogArticle = $this->getById($arguments->blogArticleId);
        $editedBlogArticle = $this->blogArticleFacade->edit($blogArticle->getId(), $this->createEditedData($blogArticle, $arguments));
        $this->setSlugsAsMain($editedBlogArticle, $arguments->slugsByDomainId);

        return $editedBlogArticle;
    }

    public function getById(int $blogArticleId): BlogArticle
    {
        try {
            return $this->blogArticleFacade->getById($blogArticleId);
        } catch (BlogArticleNotFoundException $exception) {
            throw new ToolCallException(sprintf('The blog article with ID %d does not exist.', $blogArticleId), 0, $exception);
        }
    }

    protected function createEditedData(BlogArticle $blogArticle, EditBlogArticleArguments $arguments): BlogArticleData
    {
        $blogArticleData = $this->blogArticleDataFactory->createFromBlogArticle($blogArticle);

        $this->applyInput(
            $blogArticleData,
            $arguments->names,
            $arguments->perexes,
            $arguments->descriptions,
            $arguments->blogCategoryIdsByDomainId,
            $arguments->seoByDomainId,
        );
        $this->applyDescriptionReplacements($blogArticleData, $arguments->descriptionReplacements, $arguments->descriptions);
        $this->applySlugs($blogArticleData, $blogArticle, $arguments->slugsByDomainId);

        return $blogArticleData;
    }

    /**
     * @param array<string, array<int, array{find: string, replace: string}>> $descriptionReplacements
     * @param array<string, string> $descriptions
     */
    protected function applyDescriptionReplacements(
        BlogArticleData $blogArticleData,
        array $descriptionReplacements,
        array $descriptions,
    ): void {
        foreach ($descriptionReplacements as $locale => $replacements) {
            $this->assertLocaleExists($locale);

            if (array_key_exists($locale, $descriptions)) {
                throw new ToolCallException(sprintf('The content (%s) cannot be given in both descriptions and descriptionReplacements.', $locale));
            }

            $description = $blogArticleData->descriptions[$locale] ?? '';

            foreach ($replacements as $replacement) {
                $description = $this->replaceOnce($description, $replacement['find'], $replacement['replace'], $locale);
            }

            $blogArticleData->descriptions[$locale] = trim($description) === '' ? null : $description;
        }
    }

    protected function replaceOnce(string $description, string $find, string $replace, string $locale): string
    {
        return substr_replace($description, $replace, $this->getUniquePosition($description, $find, $locale), strlen($find));
    }

    protected function getUniquePosition(string $description, string $find, string $locale): int
    {
        if ($find === '') {
            throw new ToolCallException(sprintf('The text to find in the content (%s) must not be empty.', $locale));
        }

        $occurrences = substr_count($description, $find);
        $shortenedFind = mb_strimwidth($find, 0, 100, '…');

        if ($occurrences === 0) {
            throw new ToolCallException(sprintf('The text to find was not found in the content (%s): "%s". Read the current content and copy the exact part of it.', $locale, $shortenedFind));
        }

        if ($occurrences > 1) {
            throw new ToolCallException(sprintf('The text to find is in the content (%s) %d times, it must be there exactly once: "%s". Use a longer part of the content.', $locale, $occurrences, $shortenedFind));
        }

        return (int)strpos($description, $find);
    }

    /**
     * @param array<string, string> $names
     * @param array<string, string> $perexes
     * @param array<string, string> $descriptions
     * @param array<int, int[]> $blogCategoryIdsByDomainId
     * @param array<int, array{title?: string|null, metaDescription?: string|null, h1?: string|null}> $seoByDomainId
     */
    protected function applyInput(
        BlogArticleData $blogArticleData,
        array $names,
        array $perexes,
        array $descriptions,
        array $blogCategoryIdsByDomainId,
        array $seoByDomainId,
    ): void {
        foreach ($names as $locale => $name) {
            $this->assertLocaleExists($locale);
            $blogArticleData->names[$locale] = $name;
        }

        foreach ($perexes as $locale => $perex) {
            $this->assertLocaleExists($locale);
            $blogArticleData->perexes[$locale] = trim($perex) === '' ? null : $perex;
        }

        foreach ($descriptions as $locale => $description) {
            $this->assertLocaleExists($locale);
            $blogArticleData->descriptions[$locale] = trim($description) === '' ? null : $description;
        }

        foreach ($blogCategoryIdsByDomainId as $domainId => $blogCategoryIds) {
            $this->assertDomainExists($domainId);
            $blogCategories = $this->blogCategoryFacade->getByIds($blogCategoryIds);
            $missingBlogCategoryIds = array_diff($blogCategoryIds, array_map(static fn ($blogCategory) => $blogCategory->getId(), $blogCategories));

            if ($missingBlogCategoryIds !== []) {
                throw new ToolCallException(sprintf('Blog categories with IDs %s do not exist.', implode(', ', $missingBlogCategoryIds)));
            }

            $blogArticleData->blogCategoriesByDomainId[$domainId] = array_values($blogCategories);
        }

        foreach ($seoByDomainId as $domainId => $seo) {
            $this->assertDomainExists($domainId);

            foreach ($seo as $seoAttribute => $value) {
                $blogArticleData->seo[$domainId]->{$seoAttribute} = $value === null || trim($value) === '' ? null : $value;
            }
        }
    }

    /**
     * @param array<int, string> $slugsByDomainId
     */
    protected function applySlugs(
        BlogArticleData $blogArticleData,
        BlogArticle $blogArticle,
        array $slugsByDomainId,
    ): void {
        foreach ($slugsByDomainId as $domainId => $slug) {
            $this->assertDomainExists($domainId);

            $friendlyUrlSlug = ExtendedClassNameResolver::resolve(TransformStringHelper::class)::createFriendlyUrlSlug($slug);

            if ($slug === '' || $friendlyUrlSlug !== $slug) {
                throw new ToolCallException(sprintf('The slug "%s" is not valid, use only lowercase letters without diacritics, digits and dashes, for example "%s".', $slug, $friendlyUrlSlug));
            }

            $friendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug($domainId, $slug);

            if ($friendlyUrl === null) {
                $blogArticleData->urls[$domainId]->newUrls[] = $slug;

                continue;
            }

            if ($friendlyUrl->getRouteName() !== static::ROUTE_NAME || $friendlyUrl->getEntityId() !== $blogArticle->getId()) {
                throw new ToolCallException(sprintf('The slug "%s" is already used on the domain with ID %d.', $slug, $domainId));
            }
        }
    }

    /**
     * @param array<int, string> $slugsByDomainId
     */
    protected function setSlugsAsMain(BlogArticle $blogArticle, array $slugsByDomainId): void
    {
        foreach ($slugsByDomainId as $domainId => $slug) {
            $urlListData = $this->urlListDataFactory->create();
            $urlListData->mainFriendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug($domainId, $slug);

            $this->friendlyUrlFacade->saveUrlListFormDataForDomain(static::ROUTE_NAME, $blogArticle->getId(), $urlListData, $domainId);
        }
    }

    protected function assertLocaleExists(string $locale): void
    {
        if (!in_array($locale, $this->domain->getAllLocales(), true)) {
            throw new ToolCallException(sprintf('The locale "%s" does not exist, use one of: %s.', $locale, implode(', ', $this->domain->getAllLocales())));
        }
    }

    protected function assertDomainExists(int $domainId): void
    {
        if (!in_array($domainId, $this->domain->getAllIds(), true)) {
            throw new ToolCallException(sprintf('The domain with ID %d does not exist, use one of: %s.', $domainId, implode(', ', $this->domain->getAllIds())));
        }
    }
}
