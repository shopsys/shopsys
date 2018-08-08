<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class ImageEntityConfigNotFoundException extends Exception implements ImageConfigException
{
    /**
     * @var string
     */
    private $entityClassOrName;

    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $entityClassOrName, Exception $previous = null)
    {
        $this->entityClassOrName = $entityClassOrName;

        parent::__construct('Not found image config for entity "' . $entityClassOrName . '".', 0, $previous);
    }

    public function getEntityClassOrName(): string
    {
        return $this->entityClassOrName;
    }
}
