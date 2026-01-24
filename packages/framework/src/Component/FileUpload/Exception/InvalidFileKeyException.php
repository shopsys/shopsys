<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\Utils\Debug;

class InvalidFileKeyException extends Exception
{
    public function __construct(mixed $key, ?Exception $previous = null)
    {
        parent::__construct('Upload file key ' . Debug::export($key) . ' is invalid', 0, $previous);
    }
}
