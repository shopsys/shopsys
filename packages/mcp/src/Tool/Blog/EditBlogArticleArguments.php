<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Tool\Blog;

class EditBlogArticleArguments
{
    /**
     * @param array<string, string> $names
     * @param array<string, string> $perexes
     * @param array<string, string> $descriptions
     * @param array<string, array<int, array{find: string, replace: string}>> $descriptionReplacements
     * @param array<int, int[]> $blogCategoryIdsByDomainId
     * @param array<int, array{title?: string|null, metaDescription?: string|null, h1?: string|null}> $seoByDomainId
     * @param array<int, string> $slugsByDomainId
     */
    public function __construct(
        public readonly int $blogArticleId,
        public readonly array $names = [],
        public readonly array $perexes = [],
        public readonly array $descriptions = [],
        public readonly array $descriptionReplacements = [],
        public readonly array $blogCategoryIdsByDomainId = [],
        public readonly array $seoByDomainId = [],
        public readonly array $slugsByDomainId = [],
    ) {
    }
}
