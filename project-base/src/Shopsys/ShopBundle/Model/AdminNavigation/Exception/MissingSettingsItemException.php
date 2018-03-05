<?php

namespace Shopsys\FrameworkBundle\Model\AdminNavigation\Exception;

use Exception;

class MissingSettingsItemException extends Exception implements MenuException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
