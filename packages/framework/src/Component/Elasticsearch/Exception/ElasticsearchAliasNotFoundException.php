<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchAliasNotFoundException extends Exception
{
    /**
     * @param string $alias
     */
    public function __construct(string $alias)
    {
        parent::__construct(
            sprintf('Index with alias "%s" was not found.', $alias),
        );
    }
}
