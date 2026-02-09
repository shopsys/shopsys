<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem\Exception;

use Exception;

class DirectoryDoesNotExistException extends Exception
{
    public function __construct(string $path, ?Exception $previous = null)
    {
        $message = sprintf('Path "%s" must exist.', $path);

        parent::__construct($message, 0, $previous);
    }
}
