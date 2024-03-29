<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command\Exception;

use Exception;

class TranslationReplaceSourceCommandException extends Exception implements CommandException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
