<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchMoreThanOneIndexFoundForAliasException extends Exception
{
    public function __construct(string $alias, array $indexesFound)
    {
        parent::__construct(sprintf(
            'Can\'t resolve index name for alias "%s". More than one index found ("%s").',
            $alias,
            implode('", "', $indexesFound),
        ));
    }
}
