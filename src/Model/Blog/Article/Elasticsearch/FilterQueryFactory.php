<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

/**
 * Heavily inspired by @see \App\Model\Product\Search\FilterQueryFactory
 *
 * @see https://github.com/shopsys/shopsys/issues/2362
 */
class FilterQueryFactory
{
    /**
     * @param string $indexName
     * @return \App\Model\Blog\Article\Elasticsearch\FilterQuery
     */
    public function create(string $indexName): FilterQuery
    {
        return new FilterQuery($indexName);
    }
}
