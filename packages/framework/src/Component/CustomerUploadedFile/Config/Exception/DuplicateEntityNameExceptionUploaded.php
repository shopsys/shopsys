<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception;

use Exception;

class DuplicateEntityNameExceptionUploaded extends Exception implements CustomerUploadedFileConfigException
{
    protected string $entityName;

    /**
     * @param string $entityName
     * @param \Exception|null $previous
     */
    public function __construct($entityName, ?Exception $previous = null)
    {
        $this->entityName = $entityName;

        $message = sprintf('CustomerUploadedFile entity name "%s" is not unique.', $this->entityName);

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }
}
