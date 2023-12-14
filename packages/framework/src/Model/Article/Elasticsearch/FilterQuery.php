<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery;

class FilterQuery extends AbstractFilterQuery
{
    /**
     * @param string $indexName
     */
    public function __construct(string $indexName)
    {
        parent::__construct($indexName);

        $this->sorting = [
            'placement' => 'asc',
            'position' => 'asc',
        ];
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery
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
     * @return \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery
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
     * @param string[] $placements
     * @return \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery
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

    /**
     * @param int $articleId
     * @return \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\FilterQuery
     */
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
