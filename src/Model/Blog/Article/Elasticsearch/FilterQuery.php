<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

use stdClass;

/**
 * Heavily inspired by @see \App\Model\Product\Search\FilterQuery
 *
 * @see https://github.com/shopsys/shopsys/issues/2362
 */
class FilterQuery
{
    /**
     * @var array
     */
    private array $filters = [];

    /**
     * @var string
     */
    private string $indexName;

    /**
     * @var array
     */
    private array $sorting = [
        'publishDate' => 'asc',
        'name.keyword' => 'asc',
    ];

    /**
     * @var int
     */
    private int $limit = 1000;

    /**
     * @var int
     */
    private int $page = 1;

    /**
     * @var array
     */
    private array $match;

    /**
     * @var int|null
     */
    private ?int $from = null;

    /**
     * @param string $indexName
     */
    public function __construct(string $indexName)
    {
        $this->indexName = $indexName;
        $this->match = [
            'match_all' => new stdClass(),
        ];
    }

    /**
     * @param int $limit
     * @return \App\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function setLimit(int $limit): self
    {
        $clone = clone $this;

        $clone->limit = $limit;

        return $clone;
    }

    /**
     * @param int $from
     * @return \App\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function setFrom(int $from): self
    {
        $clone = clone $this;

        $clone->from = $from;

        return $clone;
    }

    /**
     * @param string $uuid
     * @return \App\Model\Blog\Article\Elasticsearch\FilterQuery
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
     * @return \App\Model\Blog\Article\Elasticsearch\FilterQuery
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
     * @return array
     */
    public function getQuery(): array
    {
        return [
            'index' => $this->indexName,
            'body' => [
                'from' => $this->from !== null ? $this->from : $this->countFrom($this->page, $this->limit),
                'size' => $this->limit,
                'sort' => $this->sorting,
                'query' => [
                    'bool' => [
                        'must' => $this->match,
                        'filter' => $this->filters,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @return int
     */
    private function countFrom(int $page, int $limit): int
    {
        return ($page - 1) * $limit;
    }
}
