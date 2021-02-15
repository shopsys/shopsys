<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

class ElasticsearchAliasUsedByDifferentIndex extends ElasticsearchIndexException
{
    /**
     * @param string $alias
     */
    public function __construct(string $alias)
    {
        parent::__construct(sprintf(
            'There is an index for alias "%s" already. You have to migrate it first due to different definition.',
            $alias
        ));
    }
}
