<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchBulkUpdateException extends Exception
{
    /**
     * @param string $indexName
     * @param array $errors
     */
    public function __construct(string $indexName, array $errors)
    {
        parent::__construct(sprintf(
            'One or more items return error when updating "%s":' . PHP_EOL . '"%s"',
            $indexName,
            json_encode($errors, JSON_THROW_ON_ERROR),
        ));
    }
}
