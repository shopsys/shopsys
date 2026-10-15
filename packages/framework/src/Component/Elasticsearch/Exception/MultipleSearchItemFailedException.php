<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class MultipleSearchItemFailedException extends Exception
{
    /**
     * @param array<string, mixed> $error
     */
    public function __construct(int|string $searchQueryKey, array $error)
    {
        parent::__construct(sprintf(
            'Elasticsearch multi search item "%s" failed: %s',
            $searchQueryKey,
            json_encode($error, JSON_THROW_ON_ERROR),
        ));
    }
}
