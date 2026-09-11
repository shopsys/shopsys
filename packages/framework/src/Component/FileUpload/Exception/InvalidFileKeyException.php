<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\FileUpload\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\Utils\Debug;

class InvalidFileKeyException extends Exception
{
    public function __construct(mixed $key, ?Exception $previous = null)
    {
        parent::__construct('Upload file key ' . ExtendedClassNameResolver::resolve(Debug::class)::export($key) . ' is invalid', 0, $previous);
    }
}
