<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class FilterQuery extends AbstractFilterQuery
{
    /**
     * @param string $indexName
     */
    public function __construct(string $indexName)
    {
        parent::__construct($indexName);

        $this->sorting = [
            'publishDate' => 'desc',
            'createdAt' => 'desc',
            'name.keyword' => 'asc',
        ];
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function filterByUuid(string $uuid): self
    {
        $clone = clone $this;
        $clone->filters[] = [
            'term' => [
                'uuid' => $uuid,
            ],
        ];

        return $clone;
    }

    /**
     * @param string $slug
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function filterBySlug(string $slug): self
    {
        $clone = clone $this;
        $clone->filters[] = [
            'term' => [
                'slug' => $slug,
            ],
        ];

        return $clone;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function filterByCategory(BlogCategory $blogCategory): self
    {
        $clone = clone $this;
        $clone->filters[] = [
            'term' => [
                'categories' => $blogCategory->getId(),
            ],
        ];

        return $clone;
    }

    /**
     * @param bool $onlyVisibleOnHomepage
     * @return \Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function onlyVisibleOnHomepage(bool $onlyVisibleOnHomepage = true): self
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
