<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception;

use Exception;

class AdvancedSearchFilterOperatorNotFoundException extends Exception
{
    /**
     * @param string $operator
     */
    public function __construct($operator = '', ?Exception $previous = null)
    {
        parent::__construct(sprintf('Operator %s not found.', $operator), 0, $previous);
    }
}
