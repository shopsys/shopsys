<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception;

use Exception;

class AdvancedSearchFilterOperatorNotAllowedException extends Exception
{
    public function __construct(string $operator = '', array $allowedOperators = [], ?Exception $previous = null)
    {
        parent::__construct(sprintf('Operator %s not allowed. Allowed: %s', $operator, implode(', ', $allowedOperators)), 0, $previous);
    }
}
