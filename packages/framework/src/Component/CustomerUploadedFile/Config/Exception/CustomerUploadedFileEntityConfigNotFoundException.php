<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception;

use Exception;

class CustomerUploadedFileEntityConfigNotFoundException extends Exception implements CustomerUploadedFileConfigException
{
    /**
     * @param string $entityClassOrName
     * @param \Exception|null $previous
     */
    public function __construct(string $entityClassOrName, ?Exception $previous = null)
    {
        $message = sprintf('Not found customer uploaded file config for entity "%s"', $entityClassOrName);

        parent::__construct($message, 0, $previous);
    }
}
