<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchInvalidJsonInDefinitionFileException extends Exception
{
    /**
     * @param string $indexName
     * @param string $definitionFilepath
     */
    public function __construct(string $indexName, string $definitionFilepath)
    {
        parent::__construct(sprintf(
            'Invalid JSON in "%s" definition file "%s"',
            $indexName,
            $definitionFilepath,
        ));
    }
}
