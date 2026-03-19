<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception;

use Exception;

class AdvancedSearchFacadeNotFoundException extends Exception
{
    public function __construct(string $type, ?Exception $previous = null)
    {
        parent::__construct(sprintf('Advanced search facade for entity type "%s" not found. Is the facade tagged with "shopsys.advanced_search_facade"?', $type), 0, $previous);
    }
}
