<?php

namespace Shopsys\FrameworkBundle\Component\Filesystem\Exception;

use Exception;

class DirectoryDoesNotExistException extends Exception implements FilesystemException
{
    public function __construct(string $path, Exception $previous = null)
    {
        $message = sprintf('Path "%s" must exist.', $path);

        parent::__construct($message, 0, $previous);
    }
}
