<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;
use Throwable;

class UploadedFileConfigurationParseException extends Exception implements UploadedFileConfigException
{
    protected string $entityClass;

    /**
     * @param string $entityClass
     * @param \Throwable|null $previous
     */
    public function __construct($entityClass, ?Throwable $previous = null)
    {
        $this->entityClass = $entityClass;

        $message = sprintf('Parsing of config entity class "%s" failed.', $this->entityClass);

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }
}
