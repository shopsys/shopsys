<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;

class DuplicateTypeNameException extends Exception implements UploadedFileConfigException
{
    /**
     * @param string $typeName
     * @param \Exception|null $previous
     */
    public function __construct(string $typeName, ?Exception $previous = null)
    {
        $message = sprintf('UploadedFile type name "%s" is not unique.', $typeName);

        parent::__construct($message, 0, $previous);
    }
}
