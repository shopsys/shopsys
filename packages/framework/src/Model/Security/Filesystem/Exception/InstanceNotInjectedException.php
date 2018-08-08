<?php

namespace Shopsys\FrameworkBundle\Model\Security\Filesystem\Exception;

use Exception;

class InstanceNotInjectedException extends Exception implements FilesystemException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
