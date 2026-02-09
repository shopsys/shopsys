<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class FilterQuery extends AbstractFilterQuery
{
    public function __construct(string $indexName)
    {
        parent::__construct($indexName);

        $this->sorting = [
            'publishDate' => 'desc',
            'createdAt' => 'desc',
            'name.keyword' => 'asc',
        ];
    }

    public function filterByUuid(string $uuid): static
    {
        $clone = clone $this;
        $clone->filters[] = [
            'term' => [
                'uuid' => $uuid,
            ],
        ];

        return $clone;
    }

    public function filterBySlug(string $slug): static
    {
        $clone = clone $this;
        $clone->filters[] = [
            'term' => [
                'slug' => $slug,
            ],
        ];

        return $clone;
    }

    public function filterByCategory(BlogCategory $blogCategory): static
    {
        $clone = clone $this;
        $clone->filters[] = [
            'term' => [
                'categories' => $blogCategory->getId(),
            ],
        ];

        return $clone;
    }

    public function onlyVisibleOnHomepage(bool $onlyVisibleOnHomepage = true): static
    {
        $clone = clone $this;

        if (!$onlyVisibleOnHomepage) {
            return $clone;
        }

        $clone->filters[] = [
            'term' => [
                'visibleOnHomepage' => true,
            ],
        ];

        return $clone;
    }
}
