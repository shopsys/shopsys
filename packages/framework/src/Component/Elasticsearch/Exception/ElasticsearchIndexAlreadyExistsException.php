<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

class ElasticsearchIndexAlreadyExistsException extends ElasticsearchIndexException
{
    /**
     * @param string $indexName
     */
    public function __construct(string $indexName)
    {
        parent::__construct(sprintf('Index "%s" already exists', $indexName));
    }
}
