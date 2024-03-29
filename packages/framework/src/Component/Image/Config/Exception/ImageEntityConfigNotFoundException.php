<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class ImageEntityConfigNotFoundException extends Exception implements ImageConfigException
{
    protected string $entityClassOrName;

    /**
     * @param string $entityClassOrName
     * @param \Exception|null $previous
     */
    public function __construct($entityClassOrName, ?Exception $previous = null)
    {
        $this->entityClassOrName = $entityClassOrName;

        parent::__construct('Not found image config for entity "' . $entityClassOrName . '".', 0, $previous);
    }

    /**
     * @return string
     */
    public function getEntityClassOrName()
    {
        return $this->entityClassOrName;
    }
}
