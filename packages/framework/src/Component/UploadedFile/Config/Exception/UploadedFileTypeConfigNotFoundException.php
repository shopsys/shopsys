<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;

class UploadedFileTypeConfigNotFoundException extends Exception implements UploadedFileConfigException
{
    /**
     * @param string $typeName
     * @param \Exception|null $previous
     */
    public function __construct(string $typeName, ?Exception $previous = null)
    {
        $message = sprintf('Uploaded file type config name "%s" not found.', $typeName);

        parent::__construct($message, 0, $previous);
    }
}
