<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchCannotReadDefinitionFileException extends Exception
{
    /**
     * @param string $definitionFilepath
     */
    public function __construct(string $definitionFilepath)
    {
        parent::__construct(sprintf(
            'Can\'t read definition file at path "%s". Please check that file exists and has permissions for reading.',
            $definitionFilepath,
        ));
    }
}
