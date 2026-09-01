<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery;

class FilterQuery extends AbstractFilterQuery
{
    public function __construct(string $indexName)
    {
        parent::__construct($indexName);

        $this->sorting = [
            'placement' => 'asc',
            'position' => 'asc',
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

    public function filterByType(string $type): static
    {
        $clone = clone $this;
        $clone->filters[] = [
            'match' => [
                'type' => $type,
            ],
        ];

        return $clone;
    }

    /**
     * @param string[] $placements
     */
    public function filterByPlacements(array $placements): static
    {
        $clone = clone $this;
        $clone->filters[] = [
            'terms' => [
                'placement' => $placements,
            ],
        ];

        return $clone;
    }

    public function filterById(int $articleId): static
    {
        $clone = clone $this;
        $clone->filters[] = [
            'term' => [
                '_id' => $articleId,
            ],
        ];

        return $clone;
    }
}
