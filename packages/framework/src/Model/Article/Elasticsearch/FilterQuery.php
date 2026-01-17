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
     * @param string[] $placements
     */
    public function filterByPlacements(array $placements): self
    {
        $clone = clone $this;
        $clone->filters[] = [
            'terms' => [
                'placement' => $placements,
            ],
        ];

        return $clone;
    }

    public function filterById(int $articleId): self
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
