<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Throwable;

class ElasticsearchNoAliasException extends ElasticsearchIndexException
{
    /**
     * @param string $alias
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $alias, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Can\'t found any index with alias "%s".', $alias),
            $code,
            $previous,
        );
    }
}
