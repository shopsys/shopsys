<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool\Blog;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use Shopsys\McpBundle\Component\Logger\McpToolCallLogger;

class EditUnpublishedBlogArticleTool
{
    protected const string TOOL_NAME = 'editUnpublishedBlogArticle';

    public function __construct(
        protected readonly BlogArticleMcpFacade $blogArticleMcpFacade,
        protected readonly McpToolCallLogger $mcpToolCallLogger,
    ) {
    }

    /**
     * @param array<string, string> $names
     * @param array<string, string> $perexes
     * @param array<string, string> $descriptions
     * @param array<string, array<int, array{find: string, replace: string}>> $descriptionReplacements
     * @param array<int, int[]> $blogCategoryIdsByDomainId
     * @param array<int, array{title?: string|null, metaDescription?: string|null, h1?: string|null}> $seoByDomainId
     * @param array<int, string> $slugsByDomainId
     * @return array{blogArticleId: int}
     */
    #[McpTool(
        name: self::TOOL_NAME,
        description: 'Edits a blog article that is a draft on all domains. The change is applied immediately. Use editPublishedBlogArticle for other articles.',
    )]
    public function editUnpublishedBlogArticle(
        #[Schema(description: 'ID of the edited blog article.', minimum: 1)]
        int $blogArticleId,
        #[Schema(type: 'object', description: BlogArticleInputSchema::NAMES . ' ' . BlogArticleInputSchema::UNCHANGED_WHEN_OMITTED, additionalProperties: BlogArticleInputSchema::NAME)]
        array $names = [],
        #[Schema(type: 'object', description: BlogArticleInputSchema::PEREXES . ' ' . BlogArticleInputSchema::UNCHANGED_WHEN_OMITTED, additionalProperties: ['type' => 'string'])]
        array $perexes = [],
        #[Schema(type: 'object', description: BlogArticleInputSchema::DESCRIPTIONS . ' ' . BlogArticleInputSchema::DESCRIPTIONS_REPLACE_ALL . ' ' . BlogArticleInputSchema::UNCHANGED_WHEN_OMITTED, additionalProperties: ['type' => 'string'])]
        array $descriptions = [],
        #[Schema(type: 'object', description: BlogArticleInputSchema::DESCRIPTION_REPLACEMENTS . ' ' . BlogArticleInputSchema::UNCHANGED_WHEN_OMITTED, additionalProperties: BlogArticleInputSchema::DESCRIPTION_REPLACEMENT_LIST)]
        array $descriptionReplacements = [],
        #[Schema(type: 'object', description: BlogArticleInputSchema::BLOG_CATEGORY_IDS_BY_DOMAIN_ID . ' ' . BlogArticleInputSchema::UNCHANGED_WHEN_OMITTED, additionalProperties: BlogArticleInputSchema::BLOG_CATEGORY_IDS)]
        array $blogCategoryIdsByDomainId = [],
        #[Schema(type: 'object', description: BlogArticleInputSchema::SEO_BY_DOMAIN_ID . ' ' . BlogArticleInputSchema::UNCHANGED_WHEN_OMITTED, additionalProperties: BlogArticleInputSchema::SEO)]
        array $seoByDomainId = [],
        #[Schema(type: 'object', description: BlogArticleInputSchema::SLUGS_BY_DOMAIN_ID . ' ' . BlogArticleInputSchema::UNCHANGED_WHEN_OMITTED, additionalProperties: BlogArticleInputSchema::SLUG)]
        array $slugsByDomainId = [],
    ): array {
        $startedAt = microtime(true);
        $arguments = new EditBlogArticleArguments(
            blogArticleId: $blogArticleId,
            names: $names,
            perexes: $perexes,
            descriptions: $descriptions,
            descriptionReplacements: $descriptionReplacements,
            blogCategoryIdsByDomainId: $blogCategoryIdsByDomainId,
            seoByDomainId: $seoByDomainId,
            slugsByDomainId: $slugsByDomainId,
        );

        if (!$this->blogArticleMcpFacade->getById($blogArticleId)->isDraftOnAllDomains()) {
            throw new ToolCallException('The blog article is not a draft on all domains, edit it with the tool editPublishedBlogArticle.');
        }

        $blogArticle = $this->blogArticleMcpFacade->edit($arguments);

        $result = [
            'blogArticleId' => $blogArticle->getId(),
        ];

        $this->mcpToolCallLogger->logSuccess(static::TOOL_NAME, get_object_vars($arguments), $result, $startedAt);

        return $result;
    }
}
