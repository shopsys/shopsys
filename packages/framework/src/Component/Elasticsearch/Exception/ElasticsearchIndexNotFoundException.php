<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchIndexNotFoundException extends Exception
{
    /**
     * @param string $indexName
     */
    public function __construct(string $indexName)
    {
        parent::__construct(sprintf(
            'Index "%s" was not found.',
            $indexName,
        ));
    }
}
