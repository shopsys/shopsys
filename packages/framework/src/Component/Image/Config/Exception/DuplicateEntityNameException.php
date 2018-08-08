<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class DuplicateEntityNameException extends Exception implements ImageConfigException
{
    /**
     * @var string
     */
    private $entityName;

    public function __construct(string $entityName, Exception $previous = null)
    {
        $this->entityName = $entityName;

        $message = sprintf('Image entity name "%s" is not unique.', $this->entityName);
        parent::__construct($message, 0, $previous);
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }
}
