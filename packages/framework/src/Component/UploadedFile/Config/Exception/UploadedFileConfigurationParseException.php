<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;
use Throwable;

class UploadedFileConfigurationParseException extends Exception
{
    public function __construct(protected string $entityClass, ?Throwable $previous = null)
    {
        $message = sprintf('Parsing of config entity class "%s" failed.', $this->entityClass);

        parent::__construct($message, 0, $previous);
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
