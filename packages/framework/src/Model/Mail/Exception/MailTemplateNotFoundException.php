<?php

namespace Shopsys\FrameworkBundle\Model\Mail\Exception;

use Exception;

class MailTemplateNotFoundException extends Exception implements MailException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
