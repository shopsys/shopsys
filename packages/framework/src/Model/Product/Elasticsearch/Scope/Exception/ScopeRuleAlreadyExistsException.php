<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception;

use Exception;

class ScopeRuleAlreadyExistsException extends Exception
{
    /**
     * @param string $scopeName
     */
    public function __construct(string $scopeName)
    {
        parent::__construct(sprintf('Scope rule "%s" already exists in the current configuration. You should probably rather use "addExportFieldsToExistingScopeRule" or "overwriteExportScopeRule" function.', $scopeName));
    }
}
