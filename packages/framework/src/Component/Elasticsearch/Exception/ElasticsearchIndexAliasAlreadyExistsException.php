<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchIndexAliasAlreadyExistsException extends Exception
{
    /**
     * @param string $indexAlias
     */
    public function __construct(string $indexAlias)
    {
        parent::__construct(sprintf(
            'There is an index for alias "%s" already. You have to migrate it first due to different definition.',
            $indexAlias,
        ));
    }
}
