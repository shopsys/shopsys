<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class DuplicateEntityNameExceptionInvalid extends InvalidImageConfigException
{
    protected string $entityName;

    /**
     * @param string $entityName
     */
    public function __construct($entityName, ?Exception $previous = null)
    {
        $this->entityName = $entityName;

        $message = sprintf('Image entity name "%s" is not unique.', $this->entityName);

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
