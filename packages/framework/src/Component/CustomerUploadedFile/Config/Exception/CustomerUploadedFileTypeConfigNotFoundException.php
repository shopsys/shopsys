<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception;

use Exception;

class CustomerUploadedFileTypeConfigNotFoundException extends Exception implements CustomerUploadedFileConfigException
{
    /**
     * @param string $typeName
     * @param \Exception|null $previous
     */
    public function __construct(string $typeName, ?Exception $previous = null)
    {
        $message = sprintf('Customer uploaded file type config name "%s" not found.', $typeName);

        parent::__construct($message, 0, $previous);
    }
}
