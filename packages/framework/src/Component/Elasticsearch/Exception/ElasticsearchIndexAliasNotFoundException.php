<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchIndexAliasNotFoundException extends Exception
{
    /**
     * @param string $aliasName
     */
    public function __construct(string $aliasName)
    {
        parent::__construct(sprintf(
            'Index with alias "%s" was not found.',
            $aliasName,
        ));
    }
}
