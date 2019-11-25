<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;

class UploadedFileEntityConfigNotFoundException extends Exception implements UploadedFileConfigException
{
    /**
     * @param string $entityClassOrName
     * @param \Exception|null $previous
     */
    public function __construct(string $entityClassOrName, ?Exception $previous = null)
    {
        $message = sprintf('Not found uploaded file config for entity "%s"', $entityClassOrName);

        parent::__construct($message, 0, $previous);
    }
}
