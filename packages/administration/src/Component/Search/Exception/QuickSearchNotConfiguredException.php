<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Exception;

use Exception;

class QuickSearchNotConfiguredException extends Exception
{
    public static function noFieldsAndNoCallback(): self
    {
        return new self('Quick search has no fields configured and no query callback set. Pass field paths to enableQuickSearch() or set a callback via queryCallback().');
    }
}
