<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;

class NotSupportedTypeNameException extends Exception implements UploadedFileConfigException
{
    /**
     * @param string|null $typeName
     * @param \Exception|null $previous
     */
    public function __construct(?string $typeName, ?Exception $previous = null)
    {
        $message = sprintf(
            'UploadedFile type name "%s" is not supported. For default type name use "%s" as value.',
            $typeName,
            UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
        );

        parent::__construct($message, 0, $previous);
    }
}
