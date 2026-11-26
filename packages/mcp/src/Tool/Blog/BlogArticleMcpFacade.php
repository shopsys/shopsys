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
use Shopsys\McpBundle\Component\WriteTool\McpChange;
use Shopsys\McpBundle\Component\WriteTool\McpDomainInputValidator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BlogArticleMcpFacade
{
    protected const int CONTEXT_LENGTH = 60;
    protected const string ROUTE_NAME = 'front_blogarticle_detail';

    public function __construct(
        protected readonly BlogArticleFacade $blogArticleFacade,
        protected readonly BlogArticleDataFactory $blogArticleDataFactory,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly UrlListDataFactory $urlListDataFactory,
        protected readonly McpDomainInputValidator $mcpDomainInputValidator,
    ) {
    }

    /**
     * @param array<string, string> $names
     * @param array<string, string> $perexes
     * @param array<string, string> $descriptions
     * @param array<int, int[]> $blogCategoryIdsByDomainId
     * @param array<int, array{title?: string|null, metaDescription?: string|null, h1?: string|null}> $seoByDomainId
     */
    public function create(
        array $names,
        array $perexes = [],
        array $descriptions = [],
        array $blogCategoryIdsByDomainId = [],
        array $seoByDomainId = [],
    ): BlogArticle {
        if ($names === []) {
            throw new ToolCallException('At least one name of the blog article must be given.');
        }

        $blogArticleData = $this->blogArticleDataFactory->create();
        $this->applyInput($blogArticleData, $names, $perexes, $descriptions, $blogCategoryIdsByDomainId, $seoByDomainId);

        return $this->blogArticleFacade->create($blogArticleData);
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

    public function describeEdit(EditBlogArticleArguments $arguments): McpChange
    {
        $blogArticle = $this->getById($arguments->blogArticleId);
        $editedData = $this->createEditedData($blogArticle, $arguments);
        $firstDomainLocale = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
        $change = new McpChange(
            BlogArticleChangeLabel::SUBJECT,
            [
                '%id%' => $blogArticle->getId(),
                '%name%' => (string)$blogArticle->getName($firstDomainLocale),
            ],
            $this->urlGenerator->generate('admin_blogarticle_edit', ['id' => $blogArticle->getId()]),
        );

        foreach (array_keys($arguments->names) as $locale) {
            $this->addChangedField($change, BlogArticleChangeLabel::NAME, ['%locale%' => $locale], $blogArticle->getName($locale), $editedData->names[$locale]);
        }

        foreach (array_keys($arguments->perexes) as $locale) {
            $this->addChangedField($change, BlogArticleChangeLabel::PEREX, ['%locale%' => $locale], $blogArticle->getPerex($locale), $editedData->perexes[$locale]);
        }

        foreach (array_keys($arguments->descriptions) as $locale) {
            $this->addChangedField($change, BlogArticleChangeLabel::CONTENT, ['%locale%' => $locale], $blogArticle->getDescription($locale), $editedData->descriptions[$locale]);
        }

        foreach ($arguments->descriptionReplacements as $locale => $replacements) {
            $this->addDescriptionReplacementFields($change, (string)$locale, $blogArticle->getDescription((string)$locale) ?? '', $replacements);
        }

        $this->addBlogCategoryFields($change, $blogArticle, $arguments->blogCategoryIdsByDomainId, $editedData);
        $this->addSeoFields($change, $blogArticle, $arguments->seoByDomainId, $editedData);
        $this->addSlugFields($change, $blogArticle, $arguments->slugsByDomainId);

        if ($change->toArray()['fields'] === []) {
            throw new ToolCallException('The input does not change the blog article.');
        }

        return $change;
    }

    /**
     * @param array<int, int[]> $blogCategoryIdsByDomainId
     */
    protected function addBlogCategoryFields(
        McpChange $change,
        BlogArticle $blogArticle,
        array $blogCategoryIdsByDomainId,
        BlogArticleData $editedData,
    ): void {
        $currentBlogCategoriesByDomainId = $blogArticle->getBlogCategoriesIndexedByDomainId();

        foreach (array_keys($blogCategoryIdsByDomainId) as $domainId) {
            $locale = $this->domain->getDomainConfigById($domainId)->getLocale();
            $this->addChangedField(
                $change,
                BlogArticleChangeLabel::CATEGORIES,
                ['%domainId%' => $domainId],
                $this->getBlogCategoryNames($currentBlogCategoriesByDomainId[$domainId] ?? [], $locale),
                $this->getBlogCategoryNames($editedData->blogCategoriesByDomainId[$domainId], $locale),
            );
        }
    }

    /**
     * @param array<int, string> $slugsByDomainId
     */
    protected function addSlugFields(McpChange $change, BlogArticle $blogArticle, array $slugsByDomainId): void
    {
        foreach ($slugsByDomainId as $domainId => $slug) {
            $this->addChangedField(
                $change,
                BlogArticleChangeLabel::SLUG,
                ['%domainId%' => $domainId],
                $this->friendlyUrlFacade->findMainFriendlyUrl((int)$domainId, static::ROUTE_NAME, $blogArticle->getId())?->getSlug(),
                $slug,
            );
        }
    }

    /**
     * @param array<int, array{title?: string|null, metaDescription?: string|null, h1?: string|null}> $seoByDomainId
     */
    protected function addSeoFields(
        McpChange $change,
        BlogArticle $blogArticle,
        array $seoByDomainId,
        BlogArticleData $editedData,
    ): void {
        foreach ($seoByDomainId as $domainId => $seo) {
            $currentSeoAttributes = $blogArticle->getSeoAttributes($domainId);
            $currentSeo = [
                'title' => $currentSeoAttributes->getTitle(),
                'metaDescription' => $currentSeoAttributes->getMetaDescription(),
                'h1' => $currentSeoAttributes->getH1(),
            ];

            foreach (array_keys($seo) as $seoAttribute) {
                $this->addChangedField(
                    $change,
                    BlogArticleChangeLabel::SEO_BY_ATTRIBUTE[$seoAttribute],
                    ['%domainId%' => $domainId],
                    $currentSeo[$seoAttribute],
                    $editedData->seo[$domainId]->{$seoAttribute},
                );
            }
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
            $this->mcpDomainInputValidator->assertLocaleExists($locale);

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
     * @param array<int, array{find: string, replace: string}> $replacements
     */
    protected function addDescriptionReplacementFields(
        McpChange $change,
        string $locale,
        string $description,
        array $replacements,
    ): void {
        foreach ($replacements as $replacement) {
            $position = $this->getUniquePosition($description, $replacement['find'], $locale);
            $findLength = strlen($replacement['find']);

            if ($replacement['find'] !== $replacement['replace']) {
                $change->addField(
                    BlogArticleChangeLabel::CONTENT,
                    $replacement['find'],
                    $replacement['replace'],
                    ['%locale%' => $locale],
                    $this->getContextBefore(substr($description, 0, $position)),
                    $this->getContextAfter(substr($description, $position + $findLength)),
                );
            }

            $description = substr_replace($description, $replacement['replace'], $position, $findLength);
        }
    }

    protected function getContextBefore(string $text): string
    {
        if (mb_strlen($text) <= static::CONTEXT_LENGTH) {
            return $text;
        }

        $context = mb_substr($text, -static::CONTEXT_LENGTH);

        return '…' . (preg_replace('/^\S*\s/u', '', $context) ?? $context);
    }

    protected function getContextAfter(string $text): string
    {
        if (mb_strlen($text) <= static::CONTEXT_LENGTH) {
            return $text;
        }

        $context = mb_substr($text, 0, static::CONTEXT_LENGTH);

        return (preg_replace('/\s\S*$/u', '', $context) ?? $context) . '…';
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
            $this->mcpDomainInputValidator->assertLocaleExists($locale);
            $blogArticleData->names[$locale] = $name;
        }

        foreach ($perexes as $locale => $perex) {
            $this->mcpDomainInputValidator->assertLocaleExists($locale);
            $blogArticleData->perexes[$locale] = trim($perex) === '' ? null : $perex;
        }

        foreach ($descriptions as $locale => $description) {
            $this->mcpDomainInputValidator->assertLocaleExists($locale);
            $blogArticleData->descriptions[$locale] = trim($description) === '' ? null : $description;
        }

        foreach ($blogCategoryIdsByDomainId as $domainId => $blogCategoryIds) {
            $this->mcpDomainInputValidator->assertDomainExists($domainId);
            $blogCategories = $this->blogCategoryFacade->getByIds($blogCategoryIds);
            $missingBlogCategoryIds = array_diff($blogCategoryIds, array_map(static fn ($blogCategory) => $blogCategory->getId(), $blogCategories));

            if ($missingBlogCategoryIds !== []) {
                throw new ToolCallException(sprintf('Blog categories with IDs %s do not exist.', implode(', ', $missingBlogCategoryIds)));
            }

            $blogArticleData->blogCategoriesByDomainId[$domainId] = array_values($blogCategories);
        }

        foreach ($seoByDomainId as $domainId => $seo) {
            $this->mcpDomainInputValidator->assertDomainExists($domainId);

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
            $this->mcpDomainInputValidator->assertDomainExists($domainId);

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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     */
    protected function getBlogCategoryNames(array $blogCategories, string $locale): string
    {
        $blogCategoryNames = array_map(static fn ($blogCategory) => (string)$blogCategory->getName($locale), $blogCategories);
        sort($blogCategoryNames);

        return implode(', ', $blogCategoryNames);
    }

    /**
     * @param array<string, string|int> $labelParameters
     */
    protected function addChangedField(
        McpChange $change,
        string $label,
        array $labelParameters,
        ?string $before,
        ?string $after,
    ): void {
        if ($before !== $after) {
            $change->addField($label, $before, $after, $labelParameters);
        }
    }
}
