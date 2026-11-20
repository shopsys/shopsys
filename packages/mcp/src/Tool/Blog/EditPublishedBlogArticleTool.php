<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool\Blog;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use Override;
use Shopsys\McpBundle\Component\WriteTool\AbstractMcpApprovableTool;
use Shopsys\McpBundle\Component\WriteTool\McpChange;

/**
 * @extends \Shopsys\McpBundle\Component\WriteTool\AbstractMcpApprovableTool<\Shopsys\McpBundle\Tool\Blog\EditBlogArticleArguments>
 */
class EditPublishedBlogArticleTool extends AbstractMcpApprovableTool
{
    protected const string TOOL_NAME = 'editPublishedBlogArticle';

    public function __construct(
        protected readonly BlogArticleMcpFacade $blogArticleMcpFacade,
    ) {
    }

    /**
     * @param array<string, string> $names
     * @param array<string, string> $perexes
     * @param array<string, string> $descriptions
     * @param array<string, array<int, array{find: string, replace: string}>> $descriptionReplacements
     * @param array<int, int[]> $blogCategoryIdsByDomainId
     * @param array<int, array{title?: string|null, metaDescription?: string|null, h1?: string|null}> $seoByDomainId
     * @return array{status: string, changeRequestId: int, message: string}
     */
    #[McpTool(
        name: self::TOOL_NAME,
        description: 'Edits a blog article that is not a draft on all domains. The change is applied only after the administrator approves it in administration. Use editUnpublishedBlogArticle for drafts.',
    )]
    public function editPublishedBlogArticle(
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
    ): array {
        return $this->requestApproval(new EditBlogArticleArguments(
            blogArticleId: $blogArticleId,
            names: $names,
            perexes: $perexes,
            descriptions: $descriptions,
            descriptionReplacements: $descriptionReplacements,
            blogCategoryIdsByDomainId: $blogCategoryIdsByDomainId,
            seoByDomainId: $seoByDomainId,
        ));
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
        $this->assertNotDraft($arguments->blogArticleId);

        return $this->blogArticleMcpFacade->describeEdit($arguments);
    }

    /**
     * @param \Shopsys\McpBundle\Tool\Blog\EditBlogArticleArguments $arguments
     */
    #[Override]
    public function apply(object $arguments): void
    {
        $this->assertNotDraft($arguments->blogArticleId);
        $this->blogArticleMcpFacade->edit($arguments);
    }

    protected function assertNotDraft(int $blogArticleId): void
    {
        if ($this->blogArticleMcpFacade->getById($blogArticleId)->isDraftOnAllDomains()) {
            throw new ToolCallException('The blog article is a draft on all domains, edit it with the tool editUnpublishedBlogArticle.');
        }
    }
}
