<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload\Exception;

use Exception;

class MoveToFolderFailedException extends Exception
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
