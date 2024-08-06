<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileTypeConfig;

class NotSupportedTypeNameExceptionUploaded extends Exception implements CustomerUploadedFileConfigException
{
    /**
     * @param string|null $typeName
     * @param \Exception|null $previous
     */
    public function __construct(?string $typeName, ?Exception $previous = null)
    {
        $message = sprintf(
            'CustomerUploadedFile type name "%s" is not supported. For default type name use "%s" as value.',
            $typeName,
            CustomerUploadedFileTypeConfig::DEFAULT_TYPE_NAME,
        );

        parent::__construct($message, 0, $previous);
    }
}
