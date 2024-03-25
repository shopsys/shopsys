<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception;

use Exception;

class InvalidScopeException extends Exception
{
    /**
     * @param string $scopeName
     */
    public function __construct(string $scopeName)
    {
        parent::__construct(sprintf('Scope "%s" does not exist in the current configuration. Run "php bin/console shopsys:list:export-scopes" to see all the available scopes.', $scopeName));
    }
}
