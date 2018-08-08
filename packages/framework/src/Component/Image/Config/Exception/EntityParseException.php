<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class EntityParseException extends Exception implements ImageConfigException
{
    /**
     * @var string
     */
    private $entityClass;

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
