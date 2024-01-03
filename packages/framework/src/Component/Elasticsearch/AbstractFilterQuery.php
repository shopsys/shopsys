<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use stdClass;

/**
 * Heavily inspired by @see \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
 *
 * @see https://github.com/shopsys/shopsys/issues/2362
 */
abstract class AbstractFilterQuery
{
    protected const DEFAULT_LIMIT = 1000;
    protected const DEFAULT_FROM = 0;

    /**
     * @var array<int, mixed>
     */
    protected array $filters = [];

    /**
     * @var array<string, string>
     */
    protected array $sorting = [];

    protected int $limit;

    /**
     * @var array<string, \stdClass>
     */
    protected array $match;

    protected int $from;

    /**
     * @param string $indexName
     */
    public function __construct(protected string $indexName)
    {
        $this->match = [
            'match_all' => new stdClass(),
        ];
        $this->from = static::DEFAULT_FROM;
        $this->limit = static::DEFAULT_LIMIT;
    }

    /**
     * @param int|null $limit
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery
     */
    public function setLimit(?int $limit): self
    {
        $clone = clone $this;
        $clone->limit = $limit ?? static::DEFAULT_LIMIT;

        return $clone;
    }

    /**
     * @param int|null $from
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractFilterQuery
     */
    public function setFrom(?int $from): self
    {
        $clone = clone $this;
        $clone->from = $from ?? static::DEFAULT_FROM;

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
                'from' => $this->from,
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
}
