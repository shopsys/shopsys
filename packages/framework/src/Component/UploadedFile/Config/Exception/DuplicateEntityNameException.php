<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception;

use Exception;

class DuplicateEntityNameException extends Exception implements UploadedFileConfigException
{
    protected string $entityName;

    /**
     * @param string $entityName
     * @param \Exception|null $previous
     */
    public function __construct(string $entityName, ?Exception $previous = null)
    {
        $this->entityName = $entityName;

        $message = sprintf('UploadedFile entity name "%s" is not unique.', $this->entityName);

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }
}
