<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;
use Throwable;

class EntityParseException extends Exception implements ImageConfigException
{
    /**
     * @var class-string
     */
    protected $entityClass;

    /**
     * @param class-string $entityClass
     * @param \Throwable|null $previous
     */
    public function __construct(string $entityClass, ?Throwable $previous = null)
    {
        $this->entityClass = $entityClass;

        $message = sprintf('Parsing of config entity class "%s" failed.', $this->entityClass);

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
