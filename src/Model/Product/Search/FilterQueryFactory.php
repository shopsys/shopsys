<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery as BaseFilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory as BaseFilterQueryFactory;

class FilterQueryFactory extends BaseFilterQueryFactory
{
    /**
     * @param string $indexName
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function create(string $indexName): BaseFilterQuery
    {
        return new FilterQuery($indexName);
    }
}
