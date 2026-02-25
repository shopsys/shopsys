<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleStatusEnum;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;

class FilterQuery extends AbstractFilterQuery
{
    public function __construct(
        string $indexName,
        protected readonly ClockInterface $clock,
    ) {
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

    public function filterByPublishDateUpToNow(): static
    {
        $clone = clone $this;
        $clone->filters[] = [
            'range' => [
                'publishDate' => [
                    'lte' => $this->clock->now()->format('Y-m-d H:i:s'),
                ],
            ],
        ];

        return $clone;
    }

    public function filterByStatus(string $status): static
    {
        $clone = clone $this;
        $clone->filters[] = [
            'term' => [
                'status' => $status,
            ],
        ];

        return $clone;
    }

    public function excludePublishedWithFutureDate(): static
    {
        $clone = clone $this;
        $clone->filters[] = [
            'bool' => [
                'must_not' => [
                    [
                        'bool' => [
                            'must' => [
                                [
                                    'term' => [
                                        'status' => BlogArticleStatusEnum::STATUS_PUBLISHED,
                                    ],
                                ],
                                [
                                    'range' => [
                                        'publishDate' => [
                                            'gt' => $this->clock->now()->format('Y-m-d H:i:s'),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
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
