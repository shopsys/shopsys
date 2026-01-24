<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class ImageEntityConfigNotFoundException extends Exception
{
    public function __construct(protected string $entityClassOrName, ?Exception $previous = null)
    {
        parent::__construct('Not found image config for entity "' . $entityClassOrName . '".', 0, $previous);
    }

    public function getEntityClassOrName(): string
    {
        return $this->entityClassOrName;
    }
}
