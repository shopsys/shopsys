<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchCreateIndexException extends Exception
{
    /**
     * @param array<string, mixed> $error
     */
    public function __construct(string $indexName, array $error)
    {
        parent::__construct(sprintf(
            'Error when creating index "%s":' . PHP_EOL . '"%s"',
            $indexName,
            json_encode($error, JSON_THROW_ON_ERROR),
        ));
    }
}
