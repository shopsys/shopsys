<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception;

use Exception;

class ScopeRuleDoesNotExistException extends Exception
{
    /**
     * @param string $scopeName
     */
    public function __construct(string $scopeName)
    {
        parent::__construct(sprintf('Scope rule "%s" does not exist in the current configuration. You should probably rather use "addNewExportScopeRule" function.', $scopeName));
    }
}
