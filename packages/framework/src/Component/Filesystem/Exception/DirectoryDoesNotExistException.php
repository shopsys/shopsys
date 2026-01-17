<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Filesystem\Exception;

use Exception;

class DirectoryDoesNotExistException extends Exception
{
    /**
     * @param string $path
     */
    public function __construct($path, ?Exception $previous = null)
    {
        $message = sprintf('Path "%s" must exist.', $path);

        parent::__construct($message, 0, $previous);
    }
}
