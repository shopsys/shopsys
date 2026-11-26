<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool\Blog;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Override;
use Shopsys\McpBundle\Component\Logger\McpToolCallLogger;
use Shopsys\McpBundle\Component\WriteTool\AbstractMcpApprovableTool;
use Shopsys\McpBundle\Component\WriteTool\McpChange;

/**
 * @extends \Shopsys\McpBundle\Component\WriteTool\AbstractMcpApprovableTool<\Shopsys\McpBundle\Tool\Blog\EditBlogArticleArguments>
 */
class EditBlogArticleTool extends AbstractMcpApprovableTool
{
    protected const string TOOL_NAME = 'editBlogArticle';

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
     * @return array{status: string, blogArticleId: int}|array{status: string, changeRequestId: int, message: string}
     */
    #[McpTool(
        name: self::TOOL_NAME,
        description: 'Edits a blog article. When the article is a draft on all domains, the change is applied immediately (status "applied"). Otherwise the change is applied only after the administrator approves it in administration (status "pending_approval"). Get the locales and domain IDs with the tool getDomains.',
    )]
    public function editBlogArticle(
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
            return $this->requestApproval($arguments);
        }

        $result = [
            'status' => 'applied',
            'blogArticleId' => $this->blogArticleMcpFacade->edit($arguments)->getId(),
        ];

        $this->mcpToolCallLogger->logSuccess(static::TOOL_NAME, get_object_vars($arguments), $result, $startedAt);

        return $result;
    }

    #[Override]
    public static function getToolName(): string
    {
        return static::TOOL_NAME;
    }

    /**
     * @param \Shopsys\McpBundle\Tool\Blog\EditBlogArticleArguments $arguments
     */
    #[Override]
    public function describeChange(object $arguments): McpChange
    {
        return $this->blogArticleMcpFacade->describeEdit($arguments);
    }

    /**
     * @param \Shopsys\McpBundle\Tool\Blog\EditBlogArticleArguments $arguments
     */
    #[Override]
    public function apply(object $arguments): void
    {
        $this->blogArticleMcpFacade->edit($arguments);
    }
}
