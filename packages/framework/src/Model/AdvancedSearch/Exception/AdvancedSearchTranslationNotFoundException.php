<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Exception;

use Exception;

class AdvancedSearchTranslationNotFoundException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
