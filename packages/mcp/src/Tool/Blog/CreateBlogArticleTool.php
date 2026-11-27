<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool\Blog;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Shopsys\McpBundle\Component\Logger\McpToolCallLogger;

class CreateBlogArticleTool
{
    protected const string TOOL_NAME = 'createBlogArticle';

    public function __construct(
        protected readonly BlogArticleMcpFacade $blogArticleMcpFacade,
        protected readonly McpToolCallLogger $mcpToolCallLogger,
    ) {
    }

    /**
     * @param array<string, string> $names
     * @param array<string, string> $perexes
     * @param array<string, string> $descriptions
     * @param array<int, int[]> $blogCategoryIdsByDomainId
     * @param array<int, array{title?: string|null, metaDescription?: string|null, h1?: string|null}> $seoByDomainId
     * @return array{blogArticleId: int}
     */
    #[McpTool(
        name: self::TOOL_NAME,
        description: 'Creates a blog article as a draft on all domains, so it is not visible on the storefront until an administrator publishes it. Get the locales and domain IDs with the tool getDomains.',
    )]
    public function createBlogArticle(
        #[Schema(type: 'object', description: BlogArticleInputSchema::NAMES, additionalProperties: BlogArticleInputSchema::NAME)]
        array $names,
        #[Schema(type: 'object', description: BlogArticleInputSchema::PEREXES, additionalProperties: ['type' => 'string'])]
        array $perexes = [],
        #[Schema(type: 'object', description: BlogArticleInputSchema::DESCRIPTIONS, additionalProperties: ['type' => 'string'])]
        array $descriptions = [],
        #[Schema(type: 'object', description: BlogArticleInputSchema::BLOG_CATEGORY_IDS_BY_DOMAIN_ID, additionalProperties: BlogArticleInputSchema::BLOG_CATEGORY_IDS)]
        array $blogCategoryIdsByDomainId = [],
        #[Schema(type: 'object', description: BlogArticleInputSchema::SEO_BY_DOMAIN_ID, additionalProperties: BlogArticleInputSchema::SEO)]
        array $seoByDomainId = [],
    ): array {
        $arguments = get_defined_vars();
        $startedAt = microtime(true);

        $result = [
            'blogArticleId' => $this->blogArticleMcpFacade->create($names, $perexes, $descriptions, $blogCategoryIdsByDomainId, $seoByDomainId)->getId(),
        ];
        $this->mcpToolCallLogger->logSuccess(static::TOOL_NAME, $arguments, $result, $startedAt);

        return $result;
    }
}
