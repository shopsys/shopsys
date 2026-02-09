<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class DuplicateEntityNameExceptionInvalid extends InvalidImageConfigException
{
    public function __construct(protected string $entityName, ?Exception $previous = null)
    {
        $message = sprintf('Image entity name "%s" is not unique.', $this->entityName);

        parent::__construct($message, 0, $previous);
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }
}
