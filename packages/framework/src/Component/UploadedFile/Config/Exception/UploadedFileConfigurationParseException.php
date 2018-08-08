<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;

class UploadedFileConfigurationParseException extends Exception implements UploadedFileConfigException
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $entityClass, Exception $previous = null)
    {
        $this->entityClass = $entityClass;

        $message = sprintf('Parsing of config entity class "%s" failed.', $this->entityClass);
        parent::__construct($message, 0, $previous);
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
