<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception;

use Exception;

class DuplicateTypeNameExceptionUploaded extends Exception implements CustomerUploadedFileConfigException
{
    /**
     * @param string $typeName
     * @param \Exception|null $previous
     */
    public function __construct(string $typeName, ?Exception $previous = null)
    {
        $message = sprintf('CustomerUploadedFile type name "%s" is not unique.', $typeName);

        parent::__construct($message, 0, $previous);
    }
}
