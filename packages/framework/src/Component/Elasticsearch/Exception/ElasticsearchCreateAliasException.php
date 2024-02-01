<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchCreateAliasException extends Exception
{
    /**
     * @param string $alias
     * @param array $error
     */
    public function __construct(string $alias, array $error)
    {
        parent::__construct(sprintf(
            'Error when creating alias "%s":' . PHP_EOL . '"%s"',
            $alias,
            json_encode($error, JSON_THROW_ON_ERROR),
        ));
    }
}
